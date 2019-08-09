<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MigrateController extends Controller
{
    public function migrate(Request $request, string $openlistId)
    {
        // The "legacy-" prefix protects against high-jacking from another GUID.
        $materials = DB::table('materials')
            ->where(['guid' => 'legacy-' . $openlistId])
            ->update(['guid' => $request->user()->getId()]);

        // Always return success.
        return new Response('', 204);
    }
}
