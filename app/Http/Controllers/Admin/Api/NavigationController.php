<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Services\NavigationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function __construct(private readonly NavigationRepository $navigation) {}

    public function index(): JsonResponse
    {
        $menus = NavigationMenu::query()
            ->with(['items' => fn ($query) => $query->orderBy('sort_order')])
            ->get()
            ->map(fn (NavigationMenu $menu) => [
                'id' => $menu->id,
                'key' => $menu->key,
                'name' => $menu->name,
                'items' => $this->tree($menu),
            ]);

        return response()->json([
            'data' => $menus,
            'pages' => Page::query()->orderBy('title')->get(['id', 'title', 'slug']),
        ]);
    }

    public function update(Request $request, NavigationMenu $menu): JsonResponse
    {
        $data = $request->validate([
            'items' => ['present', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.label' => ['required', 'string', 'max:80'],
            'items.*.url' => ['nullable', 'string', 'max:255'],
            'items.*.page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'items.*.target' => ['nullable', 'in:_self,_blank'],
            'items.*.is_active' => ['boolean'],
            'items.*.children' => ['nullable', 'array'],
            'items.*.children.*.id' => ['nullable', 'integer'],
            'items.*.children.*.label' => ['required', 'string', 'max:80'],
            'items.*.children.*.url' => ['nullable', 'string', 'max:255'],
            'items.*.children.*.page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'items.*.children.*.is_active' => ['boolean'],
        ]);

        $menu->items()->delete();

        foreach (array_values($data['items']) as $index => $item) {
            $parent = NavigationItem::create([
                'menu_id' => $menu->id,
                'parent_id' => null,
                'label' => $item['label'],
                'url' => $item['url'] ?? null,
                'page_id' => $item['page_id'] ?? null,
                'target' => $item['target'] ?? '_self',
                'sort_order' => $index,
                'is_active' => $item['is_active'] ?? true,
            ]);

            foreach (array_values($item['children'] ?? []) as $childIndex => $child) {
                NavigationItem::create([
                    'menu_id' => $menu->id,
                    'parent_id' => $parent->id,
                    'label' => $child['label'],
                    'url' => $child['url'] ?? null,
                    'page_id' => $child['page_id'] ?? null,
                    'target' => $child['target'] ?? '_self',
                    'sort_order' => $childIndex,
                    'is_active' => $child['is_active'] ?? true,
                ]);
            }
        }

        $this->navigation->flush($menu->key);

        return response()->json(['data' => ['ok' => true]]);
    }

    /** @return array<int, array<string, mixed>> */
    private function tree(NavigationMenu $menu): array
    {
        $roots = $menu->items->whereNull('parent_id')->sortBy('sort_order')->values();

        return $roots->map(fn (NavigationItem $item) => [
            'id' => $item->id,
            'label' => $item->label,
            'url' => $item->url,
            'page_id' => $item->page_id,
            'target' => $item->target,
            'is_active' => $item->is_active,
            'resolved_url' => $item->resolvedUrl(),
            'children' => $menu->items
                ->where('parent_id', $item->id)
                ->sortBy('sort_order')
                ->map(fn (NavigationItem $child) => [
                    'id' => $child->id,
                    'label' => $child->label,
                    'url' => $child->url,
                    'page_id' => $child->page_id,
                    'target' => $child->target,
                    'is_active' => $child->is_active,
                    'resolved_url' => $child->resolvedUrl(),
                ])->values(),
        ])->all();
    }
}
