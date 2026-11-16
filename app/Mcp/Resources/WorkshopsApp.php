<?php

namespace App\Mcp\Resources;

use App\Enums\CompanyRole;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Title;

#[Title('Workshop')]
#[Description('I workshop della conferenza con i posti rimasti e un form per prenotare.')]
// Without a dedicated domain: via mcp-remote, Claude Desktop does not render the app with the domain derived from the server URL.
#[AppMeta(domain: false)]
class WorkshopsApp extends AppResource
{
    public function handle(Request $request): Response
    {
        return Response::view('mcp.workshops-app', [
            'title' => $this->title(),
            'roles' => collect(CompanyRole::cases())->map(fn (CompanyRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ])->all(),
        ]);
    }
}
