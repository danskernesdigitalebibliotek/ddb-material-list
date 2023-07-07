<?php

namespace App\Http\Controllers\v1;

use App\ListItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Log\Logger;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListController extends Controller
{
    /**
     * @var string
     */
    protected $tableName = 'materials';

    /**
     * @var string
     */
    protected $idColumn = 'material';

    /**
     * @var string
     */
    protected $idFilterName = 'material_ids';

    /**
     * @var \Illuminate\Log\Logger
     */
    protected $log;

    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    /**
     * @return mixed[]
     */
    public function get(Request $request, string $listId): array
    {
        $items = $this->getItems($request, $listId);
        $materialIds = array_map(function (ListItem $item) {
            return $item->materialId();
        }, $items);
        return [
            'id' => $listId,
            'materials' => $materialIds,
        ];
    }

    /**
     * @return ListItem[]
     */
    protected function getItems(Request $request, string $listId): array
    {
        /** @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $user */
        $user = $request->user();
        $query = DB::table($this->tableName)
            ->where(['guid' => $user->getId(), 'list' => $listId]);

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
                    try {
                        $item = ListItem::createFromString($itemId);
                        $this->idQuery($query, $item, true);
                    } catch (\InvalidArgumentException $e) {
                        throw new UnprocessableEntityHttpException($e->getMessage());
                    }
                }
            });
        }

        $items = $query->orderBy('changed_at', 'DESC')
            ->get(['material', 'collection']);

        return $items->map(function (\stdClass $item_data) {
            $id = $item_data->material ?? $item_data->collection;
            try {
                return ListItem::createFromString($id);
            } catch (\InvalidArgumentException $e) {
                $this->log->error('Unable to process stored item id: ' . $e->getMessage());
            }
        })->filter()->toArray();
    }

    public function hasItem(Request $request, string $listId, ListItem $item): Response
    {
        /** @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $user */
        $user = $request->user();
        $count = $this->idQuery(DB::table($this->tableName), $item)
            ->where(['guid' => $user->getId(), 'list' => $listId])
            ->count();

        if ($count > 0) {
            return new Response('', 200);
        } else {
            throw new NotFoundHttpException('No such item');
        }
    }

    public function addItem(Request $request, string $listId, ListItem $item): Response
    {
        /** @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $user */
        $user = $request->user();
        $this->idQuery(DB::table($this->tableName), $item)
            ->where([
                'guid' => $user->getId(),
                'list' => $listId,
            ])
            ->delete();

        DB::table($this->tableName)
            ->insert([
                'guid' => $user->getId(),
                'list' => $listId,
                $this->idColumn => urldecode($item->fullId),
                // We need to format the date ourselves to add microseconds.
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);

        return new Response('', 201);
    }

    public function removeItem(Request $request, string $listId, ListItem $item): Response
    {
        /** @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $user */
        $user = $request->user();
        $count = $this->idQuery(DB::table($this->tableName), $item)
            ->where([
                'guid' => $user->getId(),
                'list' => $listId,
            ])
            ->delete();

        return new Response('', $count > 0 ? 204 : 404);
    }

    /**
     * Create a query that deals properly with basis/katalog items.
     */
    protected function idQuery(Builder $query, ListItem $item, bool $useOr = true): Builder
    {
        $idQuery = $query->newQuery();
        // Accept matches for both materials (v1) and collections (v2).
        if (!in_array($item->base, ['katalog', 'basis'])) {
            $idQuery->where('material', '=', $item->materialId());
            $idQuery->orWhere('collection', '=', $item->collectionId());
        } else {
            $idQuery->where('material', 'LIKE', '%:' . $item->id);
            $idQuery->orWhere('collection', 'LIKE', '%:' . $item->id);
        }

        $boolean = $useOr ? "or" : "and";
        return $query->addNestedWhereQuery($idQuery, $boolean);
    }

    /**
     * @return string[]
     */
    protected function commaSeparatedQueryParamToArray(string $param, Request $request): array
    {
        $param = $request->get($param);
        return (is_string($param)) ? explode(',', $param) : [];
    }
}
