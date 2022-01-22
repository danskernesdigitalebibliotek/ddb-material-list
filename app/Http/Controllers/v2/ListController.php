<?php

namespace App\Http\Controllers\v2;

use App\Enums\ListType;
use Illuminate\Http\Request;
use App\Http\Controllers\ListController as DefaultListController;

class ListController extends DefaultListController
{
    protected $idColumn = 'collection';
    protected $idFilterName = 'collection_ids';

    public function get(Request $request, ListType $type): array
    {
        return [
            'id' => $type,
            'collections' => $this->getItems($request, $type),
        ];
    }
}
