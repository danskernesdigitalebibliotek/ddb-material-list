<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            $query->whereIn('material', $ids);
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

        $count = DB::table('materials')
            ->where(['guid' => $request->user()->getId(), 'list' => $listId, 'material' => $materialId])
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

        DB::table('materials')
            ->updateOrInsert(
                [
                    'guid' => $request->user()->getId(),
                    'list' => $listId,
                    'material' => $materialId,
                ],
                [
                    // We need to format the date ourselves to add microseconds.
                    'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
                ]
            );

        return new Response('', 201);
    }

    public function removeMaterial(Request $request, string $listId, string $materialId)
    {
        $this->checkList($listId);

        $count = DB::table('materials')
            ->where([
                'guid' => $request->user()->getId(),
                'list' => $listId,
                'material' => $materialId,
            ])->delete();

        return new Response('', $count > 0 ? 204 : 404);
    }

    protected function checkList($listId)
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }
    }
}
