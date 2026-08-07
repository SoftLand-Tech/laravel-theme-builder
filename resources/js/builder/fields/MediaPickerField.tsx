import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useBuilderMedia } from '../builderMediaContext';
import { useT } from '../i18n';

interface MediaItem {
    id: number;
    url: string;
    thumb?: string;
    name: string;
}

/**
 * Theme-scoped media library (uploads + product gallery) with URL fallback.
 */
export function MediaPickerField({ value, onChange }: { value: string; onChange: (value: string) => void }) {
    const t = useT();
    const { mediaUrl, mediaUploadUrl, csrfToken } = useBuilderMedia();
    const [media, setMedia] = useState<MediaItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const [showPicker, setShowPicker] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const loadMedia = useCallback(() => {
        setLoading(true);
        setUploadError(null);
        fetch(mediaUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then((r) => (r.ok ? r.json() : { data: [] }))
            .then((data) => {
                setMedia(data.data ?? []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [mediaUrl]);

    useEffect(() => {
        if (!showPicker) {
            return;
        }
        loadMedia();
    }, [showPicker, loadMedia]);

    const handleUpload = async (file: File) => {
        setUploading(true);
        setUploadError(null);
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(mediaUploadUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            if (!response.ok) {
                setUploadError(t('Upload failed. Try again or paste a URL.'));
                return;
            }

            const payload = await response.json();
            const item = payload.data as MediaItem | undefined;
            if (item?.url) {
                setMedia((prev) => [item, ...prev.filter((m) => m.id !== item.id)]);
                onChange(item.url);
            } else {
                loadMedia();
            }
        } catch {
            setUploadError(t('Upload failed. Try again or paste a URL.'));
        } finally {
            setUploading(false);
            if (fileInputRef.current) {
                fileInputRef.current.value = '';
            }
        }
    };

    return (
        <div className="space-y-2">
            <div className="flex overflow-hidden rounded-md border border-neutral-300 bg-white shadow-sm transition focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500/20">
                <input
                    type="url"
                    dir="ltr"
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    placeholder={t('Image URL or pick from library')}
                    className="min-w-0 flex-1 border-0 bg-transparent px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-0"
                />
                <button
                    type="button"
                    onClick={() => setShowPicker(!showPicker)}
                    aria-expanded={showPicker}
                    className={`shrink-0 border-s border-neutral-200 px-3.5 py-2 text-xs font-medium transition ${
                        showPicker
                            ? 'bg-primary-50 text-primary-700'
                            : 'bg-neutral-50 text-neutral-700 hover:bg-neutral-100'
                    }`}
                >
                    {t('Browse')}
                </button>
            </div>

            {value && (
                <div className="relative overflow-hidden rounded-md border border-neutral-200 bg-neutral-100">
                    <img src={value} alt="" className="max-h-28 w-full object-contain" />
                    <button
                        type="button"
                        onClick={() => onChange('')}
                        className="absolute end-1.5 top-1.5 rounded-md bg-white/95 px-2 py-1 text-[10px] font-medium text-neutral-700 shadow-sm ring-1 ring-neutral-200 transition hover:bg-white hover:text-primary-700"
                    >
                        {t('Remove image')}
                    </button>
                </div>
            )}

            {showPicker && (
                <div className="rounded-md border border-neutral-200 bg-white p-2 shadow-sm">
                    <div className="mb-2 flex items-center justify-between gap-2 border-b border-neutral-100 pb-2">
                        <span className="text-[11px] font-medium text-neutral-600">{t('Media library')}</span>
                        <div className="flex items-center gap-2">
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                className="sr-only"
                                onChange={(e) => {
                                    const file = e.target.files?.[0];
                                    if (file) {
                                        void handleUpload(file);
                                    }
                                }}
                            />
                            <button
                                type="button"
                                disabled={uploading}
                                onClick={() => fileInputRef.current?.click()}
                                className="rounded-md bg-primary-600 px-2.5 py-1 text-[11px] font-medium text-white transition hover:bg-primary-700 disabled:opacity-60"
                            >
                                {uploading ? t('Uploading…') : t('Upload image')}
                            </button>
                        </div>
                    </div>
                    {uploadError && (
                        <p className="mb-2 px-1 text-[11px] text-red-600" role="alert">
                            {uploadError}
                        </p>
                    )}
                    {loading && (
                        <p className="px-2 py-4 text-center text-xs text-neutral-500">{t('Loading media…')}</p>
                    )}
                    {!loading && media.length === 0 && (
                        <p className="px-2 py-4 text-center text-xs leading-relaxed text-neutral-500">
                            {t('No media in library yet. Upload an image or paste a URL above.')}
                        </p>
                    )}
                    <div className="grid max-h-48 grid-cols-4 gap-2 overflow-y-auto">
                        {media.map((m) => (
                            <button
                                key={m.id}
                                type="button"
                                onClick={() => {
                                    onChange(m.url);
                                    setShowPicker(false);
                                }}
                                className={`aspect-square overflow-hidden rounded-md border transition ${
                                    value === m.url
                                        ? 'border-primary-500 ring-2 ring-primary-500/30'
                                        : 'border-neutral-200 hover:border-primary-400'
                                }`}
                            >
                                <img
                                    src={m.thumb ?? m.url}
                                    alt={m.name}
                                    className="h-full w-full object-cover"
                                />
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
