<?php

namespace Modules\AppConfigurations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AppConfigurations\Models\AppConfiguration;

class AppConfigurationController extends Controller
{
    public function index()
    {  
        
        return response()->json([
            'success' => true,
            'data' => AppConfiguration::first(),
        ]);
    }

    public function update(Request $request)
    {
        $config = AppConfiguration::firstOrFail();
        $config->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Configuration updated successfully',
            'data' => $config->fresh()
        ]);
    }
}
