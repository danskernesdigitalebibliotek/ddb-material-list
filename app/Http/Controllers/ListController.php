<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            ->orderBy('updated_at', 'DESC')
            ->select('material')
            ->pluck('material');
        return [
            'id' => $listId,
            'materials' => $materials,
        ];
    }

    public function getMaterial(Request $request, string $listId, string $materialId)
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }

        $count = DB::table('materials')
            ->where(['guid' => $request->user(), 'list' => $listId, 'material' => $materialId])
            ->count();

        if ($count > 0) {
            return new Response('', 201);
        } else {
            throw new NotFoundHttpException('No such material');
        }
    }

    public function addMaterial(Request $request, string $listId, string $materialId)
    {
        if ($listId != 'default') {
            throw new NotFoundHttpException('No such list');
        }

        $count = DB::table('materials')
            ->insert([
                'guid' => $request->user(),
                'list' => $listId,
                'material' => $materialId,
                // We need to format the dates ourselves to add microseconds.
                'created_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s.u'),
                'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);

        return new Response('', 201);
    }
}
