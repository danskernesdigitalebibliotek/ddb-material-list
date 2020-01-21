<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListController extends Controller
{
    public function get(Request $request, string $listId)
    {
        $this->checkList($listId);

        $query = DB::table('materials')
            ->where(['guid' => $request->user()->getId(), 'list' => $listId]);

        // Filter to the given materials, if supplied.
        if ($request->has('material_ids')) {
            // The OpenAPI spec defines the parameter as a comma separated
            // list. OpenAPI defaults to using "id=1&id=2" for array types,
            // but PHP expects "id[]=1&id[]=2". So rather than trying to hack
            // around that, we use the other common option of using a single
            // comma separated value and just split it up here. Looks nicer in
            // the URL.
            $ids = explode(',', $request->get('material_ids'));
            $query->where(function ($query) use ($ids) {
                foreach ($ids as $id) {
                    $this->materialQuery($query, $id, true);
                }
            });
        }

        $materials = $query->orderBy('changed_at', 'DESC')
            ->select('material')
            ->pluck('material');

        return [
            'id' => $listId,
            'materials' => $materials,
        ];
    }

    public function checkMaterial(Request $request, string $listId, string $materialId)
    {
        $this->checkList($listId);
        $this->checkMaterialId($materialId);

        $count = $this->materialQuery(DB::table('materials'), $materialId)
            ->where(['guid' => $request->user()->getId(), 'list' => $listId])
            ->count();

        if ($count > 0) {
            return new Response('', 200);
        } else {
            throw new NotFoundHttpException('No such material');
        }
    }

    public function addMaterial(Request $request, string $listId, string $materialId)
    {
        $this->checkList($listId);

        $this->materialQuery(DB::table('materials'), $materialId)
            ->where([
                'guid' => $request->user()->getId(),
                'list' => $listId,
            ])
            ->delete();

        DB::table('materials')
            ->insert([
                'guid' => $request->user()->getId(),
                'list' => $listId,
                'material' => urldecode($materialId),
                // We need to format the date ourselves to add microseconds.
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);

        return new Response('', 201);
    }

    public function removeMaterial(Request $request, string $listId, string $materialId)
    {
        $this->checkList($listId);

        $count = $this->materialQuery(DB::table('materials'), $materialId)
            ->where([
                'guid' => $request->user()->getId(),
                'list' => $listId,
            ])
            ->delete();

        return new Response('', $count > 0 ? 204 : 404);
    }

    protected function checkList(string $listId): void
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }
    }

    protected function checkMaterialId(string $materialId): array
    {
        if (!preg_match('/(\d+)-(\w+):(\d+)/', $materialId, $matches)) {
            throw new UnprocessableEntityHttpException('Invalid pid: ' . $materialId);
        }
        return [$matches[1], $matches[2], $matches[3]];
    }

    /**
     * Create a query that deals properly with basis/katalog materials.
     */
    protected function materialQuery(Builder $query, string $materialId, $useOr = true): Builder
    {
        [$agency, $base, $id] = $this->checkMaterialId(urldecode($materialId));

        $materialWhere = ['material', '=', $agency . '-' . $base . ':' . $id];

        if (\in_array($base, ['katalog', 'basis'])) {
            $materialWhere = ['material', 'LIKE', '%:' . $id];
        }

        return $useOr ? $query->orWhere([$materialWhere]) : $query->where([$materialWhere]);
    }
}
