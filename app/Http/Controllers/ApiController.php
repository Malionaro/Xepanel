<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ApiController extends Controller
{
    /**
     * Display the interactive API documentation.
     */
    public function docs()
    {
        return view('api.docs');
    }

    /**
     * Return the OpenAPI specification as JSON.
     */
    public function schema()
    {
        $baseUrl = URL::to('/');
        
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'FilePanel API',
                'description' => 'Documentation for the FilePanel management API. Use your API Key for authentication.',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => $baseUrl . '/api', 'description' => 'Main API Server']
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                    'queryParamAuth' => [
                        'type' => 'apiKey',
                        'in' => 'query',
                        'name' => 'api_token',
                    ]
                ]
            ],
            'security' => [
                ['bearerAuth' => []],
                ['queryParamAuth' => []]
            ],
            'paths' => [
                '/services' => [
                    'get' => [
                        'summary' => 'List all services',
                        'tags' => ['Services'],
                        'responses' => [
                            '200' => ['description' => 'A list of all services and their configurations.']
                        ]
                    ]
                ],
                '/services/{id}/status' => [
                    'get' => [
                        'summary' => 'Get service status',
                        'tags' => ['Services'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Current running status and PID.']
                        ]
                    ]
                ],
                '/services/{id}/start' => [
                    'post' => [
                        'summary' => 'Start a service',
                        'tags' => ['Actions'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Service start command initiated.']
                        ]
                    ]
                ],
                '/services/{id}/stop' => [
                    'post' => [
                        'summary' => 'Stop a service',
                        'tags' => ['Actions'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Service stop command initiated.']
                        ]
                    ]
                ]
            ]
        ];

        return response()->json($spec);
    }
}
