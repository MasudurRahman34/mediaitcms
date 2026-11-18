import { useEffect, useState } from 'react';
import { ArrowDown, ArrowUp, Plus, Save, Trash2 } from 'lucide-react';
import { api, endpoints } from '../lib/api';
import { errorMessage, useApi } from '../lib/useApi';
import { Button, Field, Input, PageHeader, Spinner, Toggle, useToast } from '../components/ui';

type NavItem = {
  id?: number;
  label: string;
  url: string | null;
  page_id: number | null;
  target?: string;
  is_active: boolean;
  resolved_url?: string;
  children?: NavItem[];
};

type Menu = { id: number; key: string; name: string; items: NavItem[] };

export function NavigationPage() {
  const toast = useToast();
  const { data, loading, reload } = useApi<{
    data: Menu[];
    pages: Array<{ id: number; title: string; slug: string }>;
  }>(endpoints.navigation);
  const [menus, setMenus] = useState<Menu[]>([]);
  const [activeKey, setActiveKey] = useState<string>('header');
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (data?.data) {
      setMenus(data.data);
      if (!data.data.some((menu) => menu.key === activeKey) && data.data[0]) {
        setActiveKey(data.data[0].key);
      }
    }
  }, [data, activeKey]);

  const activeMenu = menus.find((menu) => menu.key === activeKey);

  const updateItems = (items: NavItem[]) => {
    setMenus((current) =>
      current.map((menu) => (menu.key === activeKey ? { ...menu, items } : menu)),
    );
  };

  const addItem = (parentIndex?: number) => {
    if (!activeMenu) return;

    const item: NavItem = {
      label: 'New link',
      url: '',
      page_id: null,
      is_active: true,
      children: parentIndex === undefined ? [] : undefined,
    };

    if (parentIndex === undefined) {
      updateItems([...activeMenu.items, item]);
      return;
    }

    updateItems(
      activeMenu.items.map((parent, index) =>
        index === parentIndex
          ? { ...parent, children: [...(parent.children ?? []), item] }
          : parent,
      ),
    );
  };

  const move = (index: number, direction: -1 | 1, parentIndex?: number) => {
    if (!activeMenu) return;

    if (parentIndex === undefined) {
      const next = [...activeMenu.items];
      const target = index + direction;
      if (target < 0 || target >= next.length) return;
      [next[index], next[target]] = [next[target], next[index]];
      updateItems(next);
      return;
    }

    updateItems(
      activeMenu.items.map((parent, pIndex) => {
        if (pIndex !== parentIndex) return parent;
        const children = [...(parent.children ?? [])];
        const target = index + direction;
        if (target < 0 || target >= children.length) return parent;
        [children[index], children[target]] = [children[target], children[index]];
        return { ...parent, children };
      }),
    );
  };

  const save = async () => {
    if (!activeMenu) return;
    setSaving(true);

    try {
      await api.put(`${endpoints.navigation}/${activeMenu.id}`, {
        items: activeMenu.items.map((item) => ({
          label: item.label,
          url: item.url || null,
          page_id: item.page_id,
          target: item.target ?? '_self',
          is_active: item.is_active,
          children: (item.children ?? []).map((child) => ({
            label: child.label,
            url: child.url || null,
            page_id: child.page_id,
            target: child.target ?? '_self',
            is_active: child.is_active,
          })),
        })),
      });
      toast.push('Menu saved.');
      await reload();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    } finally {
      setSaving(false);
    }
  };

  if (loading && !data) {
    return (
      <div className="flex justify-center py-20">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Navigation"
        description="Control the header and footer menus without touching code."
        actions={
          <Button icon={<Save size={15} />} onClick={save} loading={saving}>
            Save menu
          </Button>
        }
      />

      <div className="flex flex-wrap gap-2">
        {menus.map((menu) => (
          <button
            key={menu.key}
            type="button"
            onClick={() => setActiveKey(menu.key)}
            className={`rounded-lg border px-3.5 py-2 text-sm transition ${
              activeKey === menu.key
                ? 'border-accent/50 bg-accent/15 text-ink'
                : 'border-line text-ink/55 hover:text-ink'
            }`}
          >
            {menu.name}
          </button>
        ))}
      </div>

      {activeMenu && (
        <div className="admin-card p-5">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-sm font-semibold text-ink">{activeMenu.name} items</h2>
            <Button size="sm" variant="subtle" icon={<Plus size={13} />} onClick={() => addItem()}>
              Add link
            </Button>
          </div>

          <ul className="space-y-3">
            {activeMenu.items.map((item, index) => (
              <li key={index} className="border-line rounded-lg border p-3">
                <div className="flex flex-wrap items-center gap-2">
                  <Input
                    value={item.label}
                    onChange={(event) =>
                      updateItems(
                        activeMenu.items.map((entry, i) =>
                          i === index ? { ...entry, label: event.target.value } : entry,
                        ),
                      )
                    }
                    placeholder="Label"
                    className="flex-1"
                  />

                  <select
                    value={item.page_id ?? ''}
                    onChange={(event) =>
                      updateItems(
                        activeMenu.items.map((entry, i) =>
                          i === index
                            ? {
                                ...entry,
                                page_id: event.target.value ? Number(event.target.value) : null,
                                url: event.target.value ? null : entry.url,
                              }
                            : entry,
                        ),
                      )
                    }
                    className="admin-input w-44"
                  >
                    <option value="">Custom URL</option>
                    {data?.pages.map((page) => (
                      <option key={page.id} value={page.id}>
                        {page.title}
                      </option>
                    ))}
                  </select>

                  {!item.page_id && (
                    <Input
                      value={item.url ?? ''}
                      onChange={(event) =>
                        updateItems(
                          activeMenu.items.map((entry, i) =>
                            i === index ? { ...entry, url: event.target.value } : entry,
                          ),
                        )
                      }
                      placeholder="/contact"
                      className="w-44"
                    />
                  )}

                  <Toggle
                    checked={item.is_active}
                    onChange={(value) =>
                      updateItems(
                        activeMenu.items.map((entry, i) =>
                          i === index ? { ...entry, is_active: value } : entry,
                        ),
                      )
                    }
                  />

                  <div className="flex items-center gap-0.5">
                    <button
                      type="button"
                      onClick={() => move(index, -1)}
                      disabled={index === 0}
                      className="rounded p-1.5 text-ink/30 transition hover:text-ink disabled:opacity-25"
                      aria-label="Move up"
                    >
                      <ArrowUp size={14} />
                    </button>
                    <button
                      type="button"
                      onClick={() => move(index, 1)}
                      disabled={index === activeMenu.items.length - 1}
                      className="rounded p-1.5 text-ink/30 transition hover:text-ink disabled:opacity-25"
                      aria-label="Move down"
                    >
                      <ArrowDown size={14} />
                    </button>
                    <button
                      type="button"
                      onClick={() => updateItems(activeMenu.items.filter((_, i) => i !== index))}
                      className="rounded p-1.5 text-ink/30 transition hover:text-red-300"
                      aria-label="Remove link"
                    >
                      <Trash2 size={14} />
                    </button>
                  </div>
                </div>

                {(item.children ?? []).length > 0 && (
                  <ul className="border-line mt-3 space-y-2 border-l pl-3">
                    {(item.children ?? []).map((child, childIndex) => (
                      <li key={childIndex} className="flex flex-wrap items-center gap-2">
                        <Input
                          value={child.label}
                          onChange={(event) =>
                            updateItems(
                              activeMenu.items.map((entry, i) =>
                                i === index
                                  ? {
                                      ...entry,
                                      children: (entry.children ?? []).map((c, ci) =>
                                        ci === childIndex ? { ...c, label: event.target.value } : c,
                                      ),
                                    }
                                  : entry,
                              ),
                            )
                          }
                          className="flex-1"
                        />
                        <Input
                          value={child.url ?? ''}
                          onChange={(event) =>
                            updateItems(
                              activeMenu.items.map((entry, i) =>
                                i === index
                                  ? {
                                      ...entry,
                                      children: (entry.children ?? []).map((c, ci) =>
                                        ci === childIndex ? { ...c, url: event.target.value } : c,
                                      ),
                                    }
                                  : entry,
                              ),
                            )
                          }
                          placeholder="/services/web"
                          className="w-56"
                        />
                        <div className="flex items-center gap-0.5">
                          <button
                            type="button"
                            onClick={() => move(childIndex, -1, index)}
                            disabled={childIndex === 0}
                            className="rounded p-1.5 text-ink/30 transition hover:text-ink disabled:opacity-25"
                            aria-label="Move up"
                          >
                            <ArrowUp size={13} />
                          </button>
                          <button
                            type="button"
                            onClick={() => move(childIndex, 1, index)}
                            disabled={childIndex === (item.children ?? []).length - 1}
                            className="rounded p-1.5 text-ink/30 transition hover:text-ink disabled:opacity-25"
                            aria-label="Move down"
                          >
                            <ArrowDown size={13} />
                          </button>
                          <button
                            type="button"
                            onClick={() =>
                              updateItems(
                                activeMenu.items.map((entry, i) =>
                                  i === index
                                    ? {
                                        ...entry,
                                        children: (entry.children ?? []).filter(
                                          (_, ci) => ci !== childIndex,
                                        ),
                                      }
                                    : entry,
                                ),
                              )
                            }
                            className="rounded p-1.5 text-ink/30 transition hover:text-red-300"
                            aria-label="Remove sub-link"
                          >
                            <Trash2 size={13} />
                          </button>
                        </div>
                      </li>
                    ))}
                  </ul>
                )}

                <button
                  type="button"
                  onClick={() => addItem(index)}
                  className="mt-2 text-xs text-accent-soft transition hover:text-ink"
                >
                  + Add sub-link
                </button>
              </li>
            ))}
          </ul>

          {activeMenu.items.length === 0 && (
            <p className="py-8 text-center text-sm text-ink/35">No links in this menu yet.</p>
          )}
        </div>
      )}
    </div>
  );
}
