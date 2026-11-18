import { useState } from 'react';
import { Plus, ShieldCheck, Trash2 } from 'lucide-react';
import { api, endpoints } from '../lib/api';
import { errorMessage, fieldErrors, useApi } from '../lib/useApi';
import {
  Badge,
  Button,
  ConfirmDialog,
  Field,
  Input,
  Modal,
  PageHeader,
  Spinner,
  Tabs,
  Toggle,
  useToast,
} from '../components/ui';
import { relativeTime } from '@/lib/format';

type UserRow = {
  id: number;
  name: string;
  email: string;
  role: string | null;
  role_id: number | null;
  is_active: boolean;
  last_login_at: string | null;
  created_at: string;
};

type RoleRow = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  is_system: boolean;
  users_count: number;
  permissions: string[];
};

type PermissionGroup = {
  group: string;
  items: Array<{ id: number; name: string; slug: string }>;
};

export function UsersPage() {
  const toast = useToast();
  const { data, loading, reload } = useApi<{
    data: UserRow[];
    roles: Array<{ id: number; name: string; slug: string }>;
  }>(endpoints.users);
  const {
    data: roleData,
    loading: rolesLoading,
    reload: reloadRoles,
  } = useApi<{ data: RoleRow[]; permissions: PermissionGroup[] }>(endpoints.roles);

  const [tab, setTab] = useState('users');
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<UserRow | null>(null);
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    role_id: '',
    is_active: true,
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<UserRow | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [roleDraft, setRoleDraft] = useState<Record<number, number[]>>({});

  const openEditor = (user?: UserRow) => {
    setEditing(user ?? null);
    setErrors({});
    setForm(
      user
        ? {
            name: user.name,
            email: user.email,
            password: '',
            role_id: String(user.role_id ?? ''),
            is_active: user.is_active,
          }
        : {
            name: '',
            email: '',
            password: '',
            role_id: String(data?.roles[2]?.id ?? ''),
            is_active: true,
          },
    );
    setOpen(true);
  };

  const save = async () => {
    setSaving(true);
    setErrors({});

    try {
      const payload = {
        name: form.name,
        email: form.email,
        role_id: Number(form.role_id),
        is_active: form.is_active,
        ...(form.password ? { password: form.password } : {}),
      };

      if (editing) {
        await api.put(`${endpoints.users}/${editing.id}`, payload);
      } else {
        await api.post(endpoints.users, payload);
      }

      toast.push(editing ? 'User updated.' : 'User created.');
      setOpen(false);
      await reload();
    } catch (error) {
      setErrors(fieldErrors(error));
      toast.push(errorMessage(error), 'error');
    } finally {
      setSaving(false);
    }
  };

  const remove = async () => {
    if (!deleteTarget) return;
    setDeleting(true);
    try {
      await api.delete(`${endpoints.users}/${deleteTarget.id}`);
      toast.push('User removed.');
      setDeleteTarget(null);
      await reload();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    } finally {
      setDeleting(false);
    }
  };

  const saveRole = async (role: RoleRow) => {
    const permissionIds = roleDraft[role.id] ?? [];

    try {
      await api.put(`${endpoints.roles}/${role.id}`, { permission_ids: permissionIds });
      toast.push(`${role.name} permissions updated.`);
      await reloadRoles();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Users & roles"
        description="Manage administrator accounts and what each role can do."
        actions={
          tab === 'users' ? (
            <Button icon={<Plus size={15} />} onClick={() => openEditor()}>
              New user
            </Button>
          ) : undefined
        }
      />

      <Tabs
        tabs={[
          { id: 'users', label: 'Users' },
          { id: 'roles', label: 'Roles & permissions' },
        ]}
        active={tab}
        onChange={setTab}
      />

      {tab === 'users' && (
        <div className="admin-card overflow-hidden">
          {loading ? (
            <div className="flex justify-center py-16">
              <Spinner />
            </div>
          ) : (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th className="hidden sm:table-cell">Role</th>
                  <th>Status</th>
                  <th className="hidden md:table-cell">Last sign-in</th>
                  <th className="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {data?.data.map((user) => (
                  <tr key={user.id}>
                    <td>
                      <p className="font-medium text-ink">{user.name}</p>
                      <p className="text-xs text-ink/35">{user.email}</p>
                    </td>
                    <td className="hidden text-ink/55 sm:table-cell">{user.role ?? '—'}</td>
                    <td>
                      <Badge tone={user.is_active ? 'success' : 'danger'}>
                        {user.is_active ? 'active' : 'disabled'}
                      </Badge>
                    </td>
                    <td className="hidden text-xs text-ink/40 md:table-cell">
                      {user.last_login_at ? relativeTime(user.last_login_at) : 'never'}
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <button
                          type="button"
                          onClick={() => openEditor(user)}
                          className="rounded px-2 py-1 text-xs text-ink/45 transition hover:text-ink"
                        >
                          Edit
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(user)}
                          className="rounded p-1.5 text-ink/35 transition hover:text-red-300"
                          aria-label="Delete user"
                        >
                          <Trash2 size={15} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      )}

      {tab === 'roles' && (
        <div className="space-y-4">
          {rolesLoading && !roleData ? (
            <div className="flex justify-center py-16">
              <Spinner />
            </div>
          ) : (
            roleData?.data.map((role) => {
              const selected = roleDraft[role.id] ?? [];
              const allPermissions = roleData.permissions.flatMap((group) => group.items);
              const currentIds = selected.length
                ? selected
                : allPermissions
                    .filter((permission) => role.permissions.includes(permission.slug))
                    .map((p) => p.id);

              return (
                <div key={role.id} className="admin-card p-5">
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <div className="flex items-center gap-2">
                        <h2 className="text-sm font-semibold text-ink">{role.name}</h2>
                        {role.is_system && <Badge tone="accent">system</Badge>}
                        <Badge>{role.users_count} users</Badge>
                      </div>
                      <p className="mt-1 text-xs text-ink/40">{role.description}</p>
                    </div>

                    {role.slug === 'super-admin' ? (
                      <span className="flex items-center gap-1.5 text-xs text-ink/40">
                        <ShieldCheck size={14} />
                        Full access
                      </span>
                    ) : (
                      <Button size="sm" variant="secondary" onClick={() => saveRole(role)}>
                        Save permissions
                      </Button>
                    )}
                  </div>

                  {role.slug !== 'super-admin' && (
                    <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                      {roleData.permissions.map((group) => (
                        <div key={group.group}>
                          <p className="mb-2 text-[11px] font-semibold tracking-wide text-ink/35 uppercase">
                            {group.group}
                          </p>
                          <div className="space-y-1.5">
                            {group.items.map((permission) => (
                              <label
                                key={permission.id}
                                className="flex items-center gap-2 text-xs text-ink/65"
                              >
                                <input
                                  type="checkbox"
                                  checked={currentIds.includes(permission.id)}
                                  onChange={(event) =>
                                    setRoleDraft((current) => {
                                      const base = current[role.id] ?? currentIds;
                                      return {
                                        ...current,
                                        [role.id]: event.target.checked
                                          ? [...base, permission.id]
                                          : base.filter((id) => id !== permission.id),
                                      };
                                    })
                                  }
                                  className="border-line size-3.5 rounded bg-near-black accent-[#3b82f6]"
                                />
                                {permission.name}
                              </label>
                            ))}
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              );
            })
          )}
        </div>
      )}

      <Modal
        open={open}
        onClose={() => setOpen(false)}
        title={editing ? `Edit: ${editing.name}` : 'New user'}
        footer={
          <>
            <Button variant="ghost" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button
              onClick={save}
              loading={saving}
              disabled={!form.name || !form.email || !form.role_id}
            >
              {editing ? 'Save' : 'Create user'}
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <Field label="Name" required error={errors.name}>
            <Input
              value={form.name}
              onChange={(event) => setForm({ ...form, name: event.target.value })}
            />
          </Field>
          <Field label="Email" required error={errors.email}>
            <Input
              type="email"
              value={form.email}
              onChange={(event) => setForm({ ...form, email: event.target.value })}
            />
          </Field>
          <Field
            label={editing ? 'New password' : 'Password'}
            hint={
              editing
                ? 'Leave blank to keep the current password'
                : 'At least 10 characters with letters and numbers'
            }
            required={!editing}
            error={errors.password}
          >
            <Input
              type="password"
              value={form.password}
              onChange={(event) => setForm({ ...form, password: event.target.value })}
              autoComplete="new-password"
            />
          </Field>
          <Field label="Role" required error={errors.role_id}>
            <select
              value={form.role_id}
              onChange={(event) => setForm({ ...form, role_id: event.target.value })}
              className="admin-input"
            >
              <option value="">Select a role</option>
              {data?.roles.map((role) => (
                <option key={role.id} value={role.id}>
                  {role.name}
                </option>
              ))}
            </select>
          </Field>
          <Toggle
            checked={form.is_active}
            onChange={(value) => setForm({ ...form, is_active: value })}
            label="Account is active"
          />
        </div>
      </Modal>

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete user"
        message={`Remove ${deleteTarget?.name}? They will lose access immediately.`}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
