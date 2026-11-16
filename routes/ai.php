<?php

use App\Mcp\Servers\ConferenceServer;
use Laravel\Mcp\Facades\Mcp;

// An agent never gets tired: throttle the tool calls of each user like any other API.
Mcp::web('/mcp/conference', ConferenceServer::class)->middleware(['auth:sanctum', 'throttle:60,1']);
