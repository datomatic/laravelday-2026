<?php

namespace App\Neuron\Tools;

use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool as McpServerTool;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolOutput;
use NeuronAI\Tools\ToolPropertyFactory;

/**
 * Exposes a tool of the MCP server to the Neuron agent, in-process: same name, description, schema and handle().
 */
class McpTool extends Tool
{
    public function __construct(private McpServerTool $tool)
    {
        $definition = json_decode(json_encode($tool->toArray()), true);

        $this->name = $definition['name'];
        $this->description = $definition['description'];
        $this->properties = ToolPropertyFactory::fromSchema($definition['inputSchema']);
    }

    public function __invoke(mixed ...$arguments): ToolOutput
    {
        // The MCP tool reads its arguments from the Request the container injects into handle().
        app()->instance(Request::class, new Request(array_filter($arguments, fn ($value) => $value !== null)));

        try {
            $response = app()->call([$this->tool, 'handle']);
        } catch (ValidationException $exception) {
            return ToolOutput::error($exception->getMessage());
        } finally {
            app()->forgetInstance(Request::class);
        }

        if ($response instanceof ResponseFactory) {
            return ToolOutput::text(json_encode($response->getStructuredContent(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        /** @var Response $response */
        return $response->isError()
            ? ToolOutput::error((string) $response->content())
            : ToolOutput::text((string) $response->content());
    }
}
