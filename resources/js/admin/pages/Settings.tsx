import { useEffect, useState } from 'react';
import { Save } from 'lucide-react';
import { api, endpoints } from '../lib/api';
import { errorMessage, useApi } from '../lib/useApi';
import { Button, Field, Input, PageHeader, Spinner, Tabs, useToast } from '../components/ui';
import { ImageField } from '../components/MediaPicker';

type FieldSchema = {
  label: string;
  type: 'text' | 'textarea' | 'media' | 'boolean';
  hint?: string;
  default?: string;
};

type Schema = Record<string, { label: string; fields: Record<string, FieldSchema> }>;

type SettingsPayload = {
  schema: Schema;
  data: Record<string, { value: unknown; type: string; media_url: string | null }>;
};

export function SettingsPage() {
  const { data, loading, reload } = useApi<SettingsPayload>(endpoints.settings);
  const [values, setValues] = useState<Record<string, unknown>>({});
  const [tab, setTab] = useState('general');
  const [saving, setSaving] = useState(false);
  const toast = useToast();

  useEffect(() => {
    if (!data) return;
    setValues(
      Object.fromEntries(Object.entries(data.data).map(([key, item]) => [key, item.value])),
    );
    if (!data.schema[tab]) {
      setTab(Object.keys(data.schema)[0] ?? 'general');
    }
  }, [data, tab]);

  const schema = data?.schema ?? {};
  const groups = Object.entries(schema);

  const save = async () => {
    setSaving(true);
    try {
      await api.put(endpoints.settings, { settings: values });
      toast.push('Settings saved.');
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
        title="Website settings"
        description="Company identity, contact details, SEO defaults and the global call to action."
        actions={
          <Button icon={<Save size={15} />} onClick={save} loading={saving}>
            Save changes
          </Button>
        }
      />

      <Tabs
        tabs={groups.map(([key, group]) => ({ id: key, label: group.label }))}
        active={tab}
        onChange={setTab}
      />

      {groups
        .filter(([key]) => key === tab)
        .map(([key, group]) => (
          <div key={key} className="admin-card max-w-3xl space-y-5 p-5">
            {Object.entries(group.fields).map(([fieldKey, field]) => {
              const value = values[fieldKey];

              if (field.type === 'media') {
                return (
                  <ImageField
                    key={fieldKey}
                    label={field.label}
                    hint={field.hint}
                    value={typeof value === 'number' ? value : null}
                    onChange={(mediaId) => setValues({ ...values, [fieldKey]: mediaId })}
                  />
                );
              }

              if (field.type === 'textarea') {
                return (
                  <Field key={fieldKey} label={field.label} hint={field.hint}>
                    <textarea
                      value={typeof value === 'string' ? value : ''}
                      onChange={(event) => setValues({ ...values, [fieldKey]: event.target.value })}
                      rows={3}
                      className="admin-input"
                    />
                  </Field>
                );
              }

              return (
                <Field key={fieldKey} label={field.label} hint={field.hint}>
                  <Input
                    value={typeof value === 'string' ? value : ''}
                    onChange={(event) => setValues({ ...values, [fieldKey]: event.target.value })}
                  />
                </Field>
              );
            })}
          </div>
        ))}
    </div>
  );
}
