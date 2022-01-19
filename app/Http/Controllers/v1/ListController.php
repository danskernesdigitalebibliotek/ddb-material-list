<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\ListController as DefaultListController;

/**
 * A demonstration controller for demoing that should be used
 * when pointing the "Accept-Version" header to: "1".
 */
class ListController extends DefaultListController
{
    public function get(Request $request, string $listId)
    {
        return ['it' => 'works!'];
    }
}
