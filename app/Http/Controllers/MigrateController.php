<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

class MigrateController extends Controller
{
    public function migrate(Request $request, string $openlistId)
    {
        // The "legacy-" prefix protects against high-jacking from another GUID.
        // We have to use a raw query here as Illuminate/DB does not support
        // UPDATE IGNORE. We need this to avoid duplicate entry errors if the
        // user has added an item on a list manually which also exists in
        // the materials to be migrated.
        $query = "UPDATE IGNORE materials SET guid=:guid WHERE guid=:legacy";

        // SQLite which we use for testing requires has a bit different syntax
        // for updates which should ignore duplicate key conflicts.
        $database = config('database.default');
        if ($database) {
            $driver = config("database.connections.${database}.driver");
        } else {
            // Config does not seem to work for Dredd tests. Fall back to
            // environment variable checking.
            $driver = env('DB_CONNECTION');
        }

        if ($driver == "sqlite") {
            $query = "UPDATE OR IGNORE materials SET guid=:guid WHERE guid=:legacy";
        }

        DB::statement(
            $query,
            [
                ':legacy' => 'legacy-' . $openlistId,
                ':guid' => $request->user()->getId()
            ]
        );

        // Always return success.
        return new Response('', 204);
    }
}
