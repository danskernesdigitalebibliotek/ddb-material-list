<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListController extends Controller
{
    public function get(Request $request, string $listId)
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }

        $materials = DB::table('materials')
            ->where(['guid' => $request->user(), 'list' => $listId])
            ->pluck('material');
        return [
            'id' => $listId,
            'materials' => $materials,
        ];
    }
}
