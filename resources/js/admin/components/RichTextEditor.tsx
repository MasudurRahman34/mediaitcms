import { useEffect, useRef, useState } from 'react';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import {
  Bold,
  Code,
  Heading2,
  Heading3,
  Image as ImageIcon,
  Italic,
  Link as LinkIcon,
  List,
  ListOrdered,
  Quote,
  Redo2,
  Strikethrough,
  Undo2,
} from 'lucide-react';
import { MediaPicker } from './MediaPicker';
import { cn } from '@/lib/cn';

type Props = {
  value: string;
  onChange: (html: string) => void;
  placeholder?: string;
  className?: string;
};

export function RichTextEditor({
  value,
  onChange,
  placeholder = 'Write content…',
  className,
}: Props) {
  const [pickerOpen, setPickerOpen] = useState(false);
  const lastValue = useRef(value);

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        heading: { levels: [2, 3, 4] },
        link: {
          openOnClick: false,
          autolink: true,
          HTMLAttributes: { rel: 'noopener noreferrer' },
        },
      }),
      Image.configure({ allowBase64: false, HTMLAttributes: { loading: 'lazy' } }),
      Placeholder.configure({ placeholder }),
    ],
    content: value,
    editorProps: {
      attributes: {
        class: 'tiptap focus:outline-none',
        'aria-label': 'Rich text content',
      },
    },
    onUpdate: ({ editor: instance }) => {
      lastValue.current = instance.getHTML();
      onChange(instance.getHTML());
    },
  });

  useEffect(() => {
    if (!editor) return;
    if (value !== lastValue.current && value !== editor.getHTML()) {
      lastValue.current = value;
      editor.commands.setContent(value || '', { emitUpdate: false });
    }
  }, [editor, value]);

  const setLink = () => {
    if (!editor) return;
    const previous = editor.getAttributes('link').href as string | undefined;
    const url = window.prompt('Link URL', previous ?? 'https://');
    if (url === null) return;
    if (url === '') {
      editor.chain().focus().extendMarkRange('link').unsetLink().run();
      return;
    }
    editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
  };

  if (!editor) {
    return (
      <div
        className={cn(
          'border-line h-64 animate-pulse rounded-lg border bg-near-black/40',
          className,
        )}
      />
    );
  }

  const tools: Array<{
    icon: React.ReactNode;
    label: string;
    active?: boolean;
    action: () => void;
  }> = [
    {
      icon: <Bold size={15} />,
      label: 'Bold',
      active: editor.isActive('bold'),
      action: () => editor.chain().focus().toggleBold().run(),
    },
    {
      icon: <Italic size={15} />,
      label: 'Italic',
      active: editor.isActive('italic'),
      action: () => editor.chain().focus().toggleItalic().run(),
    },
    {
      icon: <Strikethrough size={15} />,
      label: 'Strikethrough',
      active: editor.isActive('strike'),
      action: () => editor.chain().focus().toggleStrike().run(),
    },
    {
      icon: <Heading2 size={15} />,
      label: 'Heading 2',
      active: editor.isActive('heading', { level: 2 }),
      action: () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    },
    {
      icon: <Heading3 size={15} />,
      label: 'Heading 3',
      active: editor.isActive('heading', { level: 3 }),
      action: () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
    },
    {
      icon: <List size={15} />,
      label: 'Bullet list',
      active: editor.isActive('bulletList'),
      action: () => editor.chain().focus().toggleBulletList().run(),
    },
    {
      icon: <ListOrdered size={15} />,
      label: 'Numbered list',
      active: editor.isActive('orderedList'),
      action: () => editor.chain().focus().toggleOrderedList().run(),
    },
    {
      icon: <Quote size={15} />,
      label: 'Quote',
      active: editor.isActive('blockquote'),
      action: () => editor.chain().focus().toggleBlockquote().run(),
    },
    {
      icon: <Code size={15} />,
      label: 'Code block',
      active: editor.isActive('codeBlock'),
      action: () => editor.chain().focus().toggleCodeBlock().run(),
    },
    {
      icon: <LinkIcon size={15} />,
      label: 'Link',
      active: editor.isActive('link'),
      action: setLink,
    },
    { icon: <ImageIcon size={15} />, label: 'Insert image', action: () => setPickerOpen(true) },
    { icon: <Undo2 size={15} />, label: 'Undo', action: () => editor.chain().focus().undo().run() },
    { icon: <Redo2 size={15} />, label: 'Redo', action: () => editor.chain().focus().redo().run() },
  ];

  return (
    <div
      className={cn('border-line overflow-hidden rounded-lg border bg-near-black/40', className)}
    >
      <div className="border-line bg-panel-raised/60 flex flex-wrap items-center gap-0.5 border-b px-2 py-1.5">
        {tools.map((tool, index) => (
          <button
            key={tool.label}
            type="button"
            onClick={tool.action}
            title={tool.label}
            aria-label={tool.label}
            aria-pressed={tool.active}
            className={cn(
              'rounded-md p-1.5 transition',
              tool.active
                ? 'bg-accent/20 text-accent-soft'
                : 'text-ink/50 hover:bg-white/5 hover:text-ink',
              index === 10 && 'border-line ml-1 border-l pl-2',
            )}
          >
            {tool.icon}
          </button>
        ))}
      </div>

      <EditorContent editor={editor} />

      <MediaPicker
        open={pickerOpen}
        onClose={() => setPickerOpen(false)}
        onSelect={(media) => {
          editor
            .chain()
            .focus()
            .setImage({ src: media.url, alt: media.alt ?? undefined })
            .run();
          setPickerOpen(false);
        }}
      />
    </div>
  );
}
