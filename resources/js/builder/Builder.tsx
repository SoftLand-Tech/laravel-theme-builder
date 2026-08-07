import React, { useMemo, useState } from 'react';
import {
    DndContext,
    DragOverlay,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    pointerWithin,
    useSensor,
    useSensors,
    type CollisionDetection,
    type DragEndEvent,
    type DragOverEvent,
    type DragStartEvent,
} from '@dnd-kit/core';
import { sortableKeyboardCoordinates } from '@dnd-kit/sortable';
import { BuilderHeader } from './components/BuilderHeader';
import { Canvas } from './components/Canvas';
import { Inspector } from './components/Inspector';
import { Palette } from './components/Palette';
import { SettingsPanel } from './components/SettingsPanel';
import { RevisionsPanel } from './components/RevisionsPanel';
import { ProductPagePreview } from './components/ProductPagePreview';
import { CartPagePreview } from './components/CartPagePreview';
import { CollectionPagePreview } from './components/CollectionPagePreview';
import { SettingsForm } from './components/SettingsForm';
import { PRODUCT_CARD_FIELDS, PRODUCT_PAGE_FIELDS, CART_FIELDS } from './config/settings';
import { BLOCK_SCHEMAS } from './config/blocks';
import { LocaleContext } from './localeContext';
import { BuilderMediaProvider } from './builderMediaContext';
import { useT } from './i18n';
import { useBlocks } from './hooks/useBlocks';
import { useAutosave } from './hooks/useAutosave';
import { toWire, type Block, type BlockProps } from './types/blocks';
import { defaultSettings, type ThemeSettings } from './types/settings';
import { HEADER_FIELDS, FOOTER_FIELDS } from './config/headerFooter';
import { HeaderFooterPanel } from './components/HeaderFooterPanel';
import { PagesPanel, type TemplatesShape } from './components/PagesPanel';
import { BlockWireframe } from './components/BlockWireframe';
import {
    CANVAS_DROP_AREA_ID,
    isPointInRect,
    resolvePaletteInsertIndex,
} from './paletteDropIndex';

export interface BuilderProps {
    themeId: number;
    themeName: string;
    themeSlug?: string;
    themeCssUrl?: string;
    usesDraftOverlay?: boolean;
    hasUnpublishedDraft?: boolean;
    storeName: string;
    csrfToken: string;
    endpoints: {
        saveUrl: string;
        publishUrl: string;
        promoteUrl: string;
        exitUrl: string;
        mediaUrl: string;
        mediaUploadUrl: string;
    };
    blockRegistry: Array<{ type: string; label: string; category: string; icon: string }>;
    initialBlocks: Array<{ type: string; props: Record<string, unknown> }>;
    initialSettings: Record<string, unknown>;
    initialHeader?: Record<string, unknown>;
    initialFooter?: Record<string, unknown>;
    initialTemplates?: TemplatesShape;
    currentVersion?: string;
    locale: string;
}

type SidebarTab = 'sections' | 'properties' | 'theme' | 'header-footer' | 'pages' | 'revisions';
type Page = 'home' | 'product' | 'cart' | 'collection';
type ActiveDrag = { source: 'palette' | 'block'; type?: string; id?: string; label?: string } | null;

export function PuckBuilder(props: BuilderProps) {
    const { blocks, addBlock, removeBlock, duplicateBlock, moveBlock, reorder, reorderAt, updateProps, toggleHide } =
        useBlocks(props.initialBlocks);

    const [selectedId, setSelectedId] = useState<string | null>(null);
    const [settings, setSettings] = useState<ThemeSettings>(
        () => ({ ...defaultSettings, ...(props.initialSettings as ThemeSettings) }),
    );
    const [header, setHeader] = useState<Record<string, unknown>>(() => props.initialHeader ?? {});
    const [footer, setFooter] = useState<Record<string, unknown>>(() => props.initialFooter ?? {});
    const [templates, setTemplates] = useState<TemplatesShape>(() => props.initialTemplates ?? {});
    const [locale, setLocale] = useState(props.locale);
    const [viewport, setViewport] = useState<'desktop' | 'mobile'>('desktop');
    const [sidebarTab, setSidebarTab] = useState<SidebarTab>('sections');
    const [page, setPage] = useState<Page>('home');
    const [activeDrag, setActiveDrag] = useState<ActiveDrag>(null);
    const [paletteHoverIndex, setPaletteHoverIndex] = useState<number | null>(null);
    // When the merchant clicks the inline "+" between blocks, we record the
    // target index here and switch to the Sections palette; the next palette
    // click inserts at that gap instead of appending. No raw typing required.
    const [pendingInsertAt, setPendingInsertAt] = useState<number | null>(null);
    const [hasUnpublishedDraft, setHasUnpublishedDraft] = useState(props.hasUnpublishedDraft ?? false);

    const autosave = useAutosave({
        endpoints: props.endpoints,
        csrfToken: props.csrfToken,
        onThemeMeta: (meta) => {
            if (typeof meta.hasUnpublishedDraft === 'boolean') {
                setHasUnpublishedDraft(meta.hasUnpublishedDraft);
            }
        },
        getSnapshot: () => ({ blocks: toWire(blocks), settings, header, footer, templates }),
    });

    const selectedBlock: Block | null = blocks.find((b) => b.id === selectedId) ?? null;

    const revisionsEndpoints = useMemo(
        () => ({
            revisionsUrl: `/builder/themes/${props.themeId}/revisions`,
            restoreUrl: (revisionId: number) =>
                `/builder/themes/${props.themeId}/revisions/${revisionId}/restore`,
        }),
        [props.themeId],
    );

    const builderMediaApi = useMemo(
        () => ({
            mediaUrl:
                props.endpoints.mediaUrl ?? `/builder/themes/${props.themeId}/media`,
            mediaUploadUrl:
                props.endpoints.mediaUploadUrl ?? `/builder/themes/${props.themeId}/media`,
            csrfToken: props.csrfToken,
        }),
        [props.csrfToken, props.endpoints.mediaUrl, props.endpoints.mediaUploadUrl, props.themeId],
    );

    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
        useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates }),
    );

    const blockIdSet = useMemo(() => new Set(blocks.map((b) => b.id)), [blocks]);

    const collisionDetection: CollisionDetection = (args) => {
        const source = args.active.data.current?.source;
        const isCanvasTarget = (id: string | number) => {
            const s = String(id);
            if (s === CANVAS_DROP_AREA_ID) {
                return false;
            }
            if (s.startsWith('gap-')) {
                return true;
            }

            return blockIdSet.has(s);
        };

        if (source === 'palette') {
            const pointer = args.pointerCoordinates;
            const canvasRect = args.droppableRects.get(CANVAS_DROP_AREA_ID);
            if (!pointer || !canvasRect || !isPointInRect(pointer, canvasRect)) {
                return [];
            }
            const within = pointerWithin(args).filter((c) => isCanvasTarget(c.id));
            if (within.length > 0) {
                return within;
            }

            return closestCenter(args).filter((c) => isCanvasTarget(c.id));
        }
        const within = pointerWithin(args);
        if (within.length > 0) {
            return within;
        }

        return closestCenter(args);
    };

    const handleDragStart = (e: DragStartEvent) => {
        const data = e.active.data.current as ActiveDrag;
        setActiveDrag(data ? { ...data, id: String(e.active.id) } : null);
        setPaletteHoverIndex(null);
    };

    const handleDragOver = (e: DragOverEvent) => {
        const a = e.active.data.current as { source?: string } | undefined;
        if (a?.source !== 'palette') {
            setPaletteHoverIndex(null);
            return;
        }
        const over = e.over;
        if (!over) {
            setPaletteHoverIndex(null);
            return;
        }
        const overData = over.data.current as { source?: string; index?: number } | undefined;
        setPaletteHoverIndex(resolvePaletteInsertIndex(over, overData, blocks, e.active));
    };

    const clearDragUi = () => {
        setActiveDrag(null);
        setPaletteHoverIndex(null);
    };

    const handleDragEnd = (e: DragEndEvent) => {
        const a = e.active.data.current as { source?: string; type?: string } | undefined;
        const over = e.over;
        const overData = over?.data.current as { source?: string; index?: number } | undefined;
        clearDragUi();

        if (!a || !over) return;

        if (a.source === 'palette' && a.type) {
            const atIndex = resolvePaletteInsertIndex(over, overData, blocks, e.active);
            if (atIndex === null) {
                return;
            }
            const id = addBlock(a.type, atIndex);
            setSelectedId(id);
            setSidebarTab('properties');
            autosave.save();
            return;
        }

        if (a.source === 'block') {
            if (overData?.source === 'gap' && typeof overData.index === 'number') {
                const from = blocks.findIndex((b) => b.id === String(e.active.id));
                if (from !== -1) {
                    // arrayMove removes the source first, so a forward drop into
                    // gap N lands at N-1 to match the visual gap position.
                    const target = overData.index > from ? overData.index - 1 : overData.index;
                    reorderAt(from, target);
                }
            } else if (String(e.active.id) !== String(over.id)) {
                reorder(String(e.active.id), String(over.id));
            }
            autosave.save();
        }
    };

    const handleAdd = (type: string) => {
        // An inline "+" between blocks sets `pendingInsertAt`; honor it once,
        // then clear it so subsequent palette clicks revert to append/after-selected.
        const atIndex = pendingInsertAt !== null
            ? pendingInsertAt
            : selectedBlock
                ? blocks.findIndex((b) => b.id === selectedBlock.id) + 1
                : undefined;
        const id = addBlock(type, atIndex);
        setSelectedId(id);
        setPendingInsertAt(null);
        setSidebarTab('properties');
        autosave.save();
    };

    const handleInsertAt = (index: number) => {
        // Route the merchant to the curated Sections palette and remember the
        // target gap; the next palette click inserts there. No raw typing.
        setPendingInsertAt(index);
        setSidebarTab('sections');
    };

    const cancelInsertAt = () => setPendingInsertAt(null);

    const selectBlock = (id: string | null) => {
        setSelectedId(id);
        if (id) setSidebarTab('properties');
    };

    const handleDuplicate = (id: string) => {
        const newId = duplicateBlock(id);
        setSelectedId(newId);
        setSidebarTab('properties');
        autosave.save();
    };

    const handleDelete = (id: string) => {
        removeBlock(id);
        setSelectedId((cur) => (cur === id ? null : cur));
        autosave.save();
    };

    const handleMove = (id: string, dir: -1 | 1) => {
        moveBlock(id, dir);
        autosave.save();
    };

    const handleToggleHide = (id: string) => {
        toggleHide(id);
        autosave.save();
    };

    const handlePatch = (id: string, partial: BlockProps) => {
        updateProps(id, partial);
        autosave.save();
    };

    const handlePropsChange = (partial: BlockProps) => {
        if (!selectedBlock) return;
        updateProps(selectedBlock.id, partial);
        autosave.save();
    };

    const handleSettingsChange = (next: ThemeSettings) => {
        setSettings(next);
        autosave.save();
    };

    const handleHeaderChange = (next: Record<string, unknown>) => {
        setHeader(next);
        autosave.save();
    };

    const handleFooterChange = (next: Record<string, unknown>) => {
        setFooter(next);
        autosave.save();
    };

    const handleTemplatesChange = (next: TemplatesShape) => {
        setTemplates(next);
        autosave.save();
    };

    return (
        <LocaleContext.Provider value={locale}>
            <BuilderMediaProvider value={builderMediaApi}>
            <div className="flex h-screen flex-col bg-neutral-100 text-neutral-900">
                <BuilderHeader
                    themeName={props.themeName}
                    storeName={props.storeName}
                    saveStatus={autosave.status}
                    usesDraftOverlay={props.usesDraftOverlay ?? false}
                    hasUnpublishedDraft={hasUnpublishedDraft}
                    lastPublished={autosave.lastPublished}
                    lastVersion={autosave.lastVersion ?? (props.currentVersion || null)}
                    nextMinorVersion={bumpMinor(autosave.lastVersion ?? props.currentVersion ?? '')}
                    onPublish={(opts) => void autosave.publish(opts)}
                    onPromote={() => void autosave.promote()}
                    onSaveAndExit={() => void autosave.saveAndExit()}
                    onExit={autosave.exit}
                    locale={locale}
                    onLocaleChange={setLocale}
                    viewport={viewport}
                    onViewportChange={setViewport}
                    sidebarTab={sidebarTab}
                    onSidebarTabChange={setSidebarTab}
                />

                <DndContext
                    sensors={sensors}
                    collisionDetection={collisionDetection}
                    onDragStart={handleDragStart}
                    onDragOver={handleDragOver}
                    onDragEnd={handleDragEnd}
                    onDragCancel={clearDragUi}
                >
                    <div className="flex flex-1 overflow-hidden">
                        {page === 'home' && (
                            <Canvas
                                blocks={blocks}
                                selectedId={selectedId}
                                locale={locale}
                                viewport={viewport}
                                settings={settings}
                                colorSchemes={settings.color_schemes}
                                themeSlug={props.themeSlug}
                                themeCssUrl={props.themeCssUrl}
                                onSelect={selectBlock}
                                onDuplicate={handleDuplicate}
                                onDelete={handleDelete}
                                onMove={handleMove}
                                onToggleHide={handleToggleHide}
                                onPatch={handlePatch}
                                onInsertAt={handleInsertAt}
                                paletteHoverIndex={paletteHoverIndex}
                            />
                        )}
                        {page === 'product' && (
                            <ProductPagePreview
                                settings={settings}
                                locale={locale}
                                viewport={viewport}
                                templates={templates}
                                colorSchemes={settings.color_schemes}
                                themeSlug={props.themeSlug}
                            />
                        )}
                        {page === 'cart' && (
                            <CartPagePreview
                                settings={settings}
                                locale={locale}
                                viewport={viewport}
                                templates={templates}
                                colorSchemes={settings.color_schemes}
                                themeSlug={props.themeSlug}
                            />
                        )}
                        {page === 'collection' && (
                            <CollectionPagePreview
                                settings={settings}
                                locale={locale}
                                viewport={viewport}
                                templates={templates}
                                colorSchemes={settings.color_schemes}
                                themeSlug={props.themeSlug}
                            />
                        )}

                        <Sidebar
                            page={page}
                            onPreviewPageChange={setPage}
                            sidebarTab={sidebarTab}
                            blockRegistry={props.blockRegistry}
                            onAdd={handleAdd}
                            selectedBlock={selectedBlock}
                            onPropsChange={handlePropsChange}
                            settings={settings}
                            onSettingsChange={handleSettingsChange}
                            revisionsEndpoints={revisionsEndpoints}
                            csrfToken={props.csrfToken}
                            colorSchemes={settings.color_schemes}
                            header={header}
                            footer={footer}
                            onHeaderChange={handleHeaderChange}
                            onFooterChange={handleFooterChange}
                            templates={templates}
                            onTemplatesChange={handleTemplatesChange}
                            pendingInsertAt={pendingInsertAt}
                            onCancelInsertAt={cancelInsertAt}
                        />
                    </div>

                    <DragOverlay dropAnimation={null}>
                        {activeDrag?.source === 'palette' ? (
                            <div className="flex w-36 flex-col gap-1 rounded-lg border border-primary-400 bg-white p-2 shadow-xl">
                                <BlockWireframe type={activeDrag.type ?? ''} />
                                <span className="text-[11px] font-medium text-neutral-800">
                                    {activeDrag.label ?? activeDrag.type}
                                </span>
                            </div>
                        ) : activeDrag?.source === 'block' && activeDrag.id ? (
                            <BlockDragPreview block={blocks.find((b) => b.id === activeDrag.id) ?? null} locale={locale} />
                        ) : null}
                    </DragOverlay>
                </DndContext>
            </div>
            </BuilderMediaProvider>
        </LocaleContext.Provider>
    );
}

function Sidebar({
    page,
    onPreviewPageChange,
    sidebarTab,
    blockRegistry,
    onAdd,
    selectedBlock,
    onPropsChange,
    settings,
    onSettingsChange,
    revisionsEndpoints,
    csrfToken,
    colorSchemes,
    header,
    footer,
    onHeaderChange,
    onFooterChange,
    templates,
    onTemplatesChange,
    pendingInsertAt,
    onCancelInsertAt,
}: {
    page: Page;
    onPreviewPageChange: (page: Page) => void;
    sidebarTab: SidebarTab;
    blockRegistry: Array<{ type: string; label: string; category: string; icon: string }>;
    onAdd: (type: string) => void;
    selectedBlock: Block | null;
    onPropsChange: (partial: BlockProps) => void;
    settings: ThemeSettings;
    onSettingsChange: (s: ThemeSettings) => void;
    revisionsEndpoints: { revisionsUrl: string; restoreUrl: (id: number) => string };
    csrfToken: string;
    colorSchemes?: import('./types/settings').ColorScheme[];
    header: Record<string, unknown>;
    footer: Record<string, unknown>;
    onHeaderChange: (next: Record<string, unknown>) => void;
    onFooterChange: (next: Record<string, unknown>) => void;
    templates: TemplatesShape;
    onTemplatesChange: (next: TemplatesShape) => void;
    pendingInsertAt: number | null;
    onCancelInsertAt: () => void;
}) {
    const t = useT();

    return (
        <aside className="flex w-72 shrink-0 flex-col overflow-y-auto border-s border-neutral-200 bg-white p-4">
            {page !== 'home' && (
                <button
                    type="button"
                    onClick={() => onPreviewPageChange('home')}
                    className="-mt-1 mb-4 inline-flex items-center gap-1.5 self-start text-sm font-medium text-primary-600 transition hover:text-primary-500"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        className="h-4 w-4 rtl:rotate-180"
                        aria-hidden
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    {t('Back to home')}
                </button>
            )}
            {sidebarTab === 'sections' && page === 'home' && (
                <Palette
                    blocks={blockRegistry}
                    onAdd={onAdd}
                    pendingInsertAt={pendingInsertAt}
                    onCancelInsertAt={onCancelInsertAt}
                />
            )}
            {sidebarTab === 'sections' && page !== 'home' && (
                <p className="text-sm text-neutral-500">
                    {t('Home page sections are edited in the Components tab. Switch to Home under Pages to preview the storefront home page.')}
                </p>
            )}
            {sidebarTab === 'properties' && page === 'home' && (
                <Inspector block={selectedBlock} onPropsChange={onPropsChange} colorSchemes={colorSchemes} />
            )}
            {sidebarTab === 'properties' && page === 'product' && (
                <div className="space-y-6">
                    <h2 className="text-sm font-semibold text-neutral-900">{t('Product page')}</h2>
                    <SettingsForm
                        fields={PRODUCT_PAGE_FIELDS}
                        value={settings.product_page as Record<string, unknown>}
                        onChange={(next) => onSettingsChange({ ...settings, product_page: next as ThemeSettings['product_page'] })}
                    />
                    <h2 className="pt-2 text-sm font-semibold text-neutral-900">{t('Product card')}</h2>
                    <SettingsForm
                        fields={PRODUCT_CARD_FIELDS}
                        value={settings.product_card as Record<string, unknown>}
                        onChange={(next) => onSettingsChange({ ...settings, product_card: next as ThemeSettings['product_card'] })}
                    />
                </div>
            )}
            {sidebarTab === 'properties' && page === 'cart' && (
                <div className="space-y-6">
                    <h2 className="text-sm font-semibold text-neutral-900">{t('Cart page')}</h2>
                    <SettingsForm
                        fields={CART_FIELDS}
                        value={settings.cart as Record<string, unknown>}
                        onChange={(next) => onSettingsChange({ ...settings, cart: next as ThemeSettings['cart'] })}
                    />
                </div>
            )}
            {sidebarTab === 'theme' && (
                <SettingsPanel settings={settings} onChange={onSettingsChange} />
            )}
            {sidebarTab === 'header-footer' && (
                <HeaderFooterPanel
                    header={header}
                    footer={footer}
                    onHeaderChange={onHeaderChange}
                    onFooterChange={onFooterChange}
                    headerFields={HEADER_FIELDS}
                    footerFields={FOOTER_FIELDS}
                />
            )}
            {sidebarTab === 'pages' && (
                <PagesPanel
                    previewPage={page}
                    onPreviewPageChange={onPreviewPageChange}
                    templates={templates}
                    onChange={onTemplatesChange}
                />
            )}
            {sidebarTab === 'revisions' && (
                <RevisionsPanel
                    endpoints={revisionsEndpoints}
                    csrfToken={csrfToken}
                    onRestored={() => window.location.reload()}
                />
            )}
        </aside>
    );
}

function BlockDragPreview({ block, locale }: { block: Block | null; locale: string }) {
    if (!block) return null;
    const Preview = BLOCK_SCHEMAS[block.type]?.Component;
    if (!Preview) return null;
    return (
        <div className="w-full max-w-[420px] opacity-80">
            <Preview {...block.props} locale={locale} />
        </div>
    );
}

/** Compute the next minor version from a "x.y" string (empty/invalid → "1.0"). */
function bumpMinor(version: string): string {
    const m = version.match(/^(\d+)\.(\d+)$/);
    if (!m) return '1.0';
    return `${Number(m[1])}.${Number(m[2]) + 1}`;
}
