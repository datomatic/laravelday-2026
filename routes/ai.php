<?php

use App\Mcp\Servers\ConferenceServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/conference', ConferenceServer::class)->middleware('auth:sanctum');
