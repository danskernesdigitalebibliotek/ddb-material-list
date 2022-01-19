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
    protected $tableName = 'materials';
    protected $idColumn = 'material';
    protected $idFilterName = 'material_ids';

    public function get(Request $request, string $listId): array
    {
        [$listId, $materials] = $this->getItems($request, $listId);

        return [
            'id' => $listId,
            'materials' => $materials,
        ];
    }

    protected function getItems(Request $request, string $listId): array
    {
        $this->checkList($listId);

        $query = DB::table($this->tableName)
            ->where(['guid' => $request->user()->getId(), 'list' => $listId]);

        // Filter to the given items, if supplied.
        $itemIds = $this->commaSeparatedQueryParamToArray($this->idFilterName, $request);
        if (count($itemIds)) {
            // The OpenAPI spec defines the parameter as a comma separated
            // list. OpenAPI defaults to using "id=1&id=2" for array types,
            // but PHP expects "id[]=1&id[]=2". So rather than trying to hack
            // around that, we use the other common option of using a single
            // comma separated value and just split it up here. Looks nicer in
            // the URL.
            $query->where(function ($query) use ($itemIds) {
                foreach ($itemIds as $itemId) {
                    $this->idQuery($query, $itemId, true);
                }
            });
        }

        $items = $query->orderBy('changed_at', 'DESC')
            ->select($this->idColumn)
            ->pluck($this->idColumn);

        return [$listId, $items];
    }

    public function itemAvailability(Request $request, string $listId, string $itemId): Response
    {
        $this->checkList($listId);
        $this->checkId($itemId);

        $count = $this->idQuery(DB::table($this->tableName), $itemId)
            ->where(['guid' => $request->user()->getId(), 'list' => $listId])
            ->count();

        if ($count > 0) {
            return new Response('', 200);
        } else {
            throw new NotFoundHttpException('No such item');
        }
    }

    public function addItem(Request $request, string $listId, string $itemId): Response
    {
        $this->checkList($listId);

        $this->idQuery(DB::table($this->tableName), $itemId)
            ->where([
                'guid' => $request->user()->getId(),
                'list' => $listId,
            ])
            ->delete();

        DB::table($this->tableName)
            ->insert([
                'guid' => $request->user()->getId(),
                'list' => $listId,
                $this->idColumn => urldecode($itemId),
                // We need to format the date ourselves to add microseconds.
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);

        return new Response('', 201);
    }

    public function removeItem(Request $request, string $listId, string $itemId): Response
    {
        $this->checkList($listId);

        $count = $this->idQuery(DB::table($this->tableName), $itemId)
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

    protected function checkId(string $itemId): array
    {
        if (!preg_match('/(\d+)-(\w+):(\w+)/', $itemId, $matches)) {
            throw new UnprocessableEntityHttpException('Invalid pid: ' . $itemId);
        }
        return [$matches[1], $matches[2], $matches[3]];
    }

    /**
     * Create a query that deals properly with basis/katalog items.
     */
    protected function idQuery(Builder $query, string $itemId, $useOr = true): Builder
    {
        [$agency, $base, $itemId] = $this->checkId(urldecode($itemId));

        $idWhere = [$this->idColumn, '=', $agency . '-' . $base . ':' . $itemId];

        if (\in_array($base, ['katalog', 'basis'])) {
            $idWhere = [$this->idColumn, 'LIKE', '%:' . $itemId];
        }

        return $useOr ? $query->orWhere([$idWhere]) : $query->where([$idWhere]);
    }

    protected function commaSeparatedQueryParamToArray(string $param, Request $request): array
    {
        if ($request->has($param)) {
            return explode(',', $request->get($param)) ?? [];
        }

        return [];
    }
}
