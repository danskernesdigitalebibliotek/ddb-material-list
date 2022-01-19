<?php

namespace App\Http\Controllers\v2;

use Illuminate\Http\Request;
use App\Http\Controllers\ListController as DefaultListController;

/**
 * A demonstration controller for demoing that should be used
 * when pointing the "Accept-Version" header to: "2".
 */
class ListController extends DefaultListController
{
    public function get(Request $request, string $listId)
    {
        return ['it' => 'works!'];
    }
}
