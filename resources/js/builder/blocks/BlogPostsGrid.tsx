import React from 'react';
import { loc } from './_shared';
import { useViewport } from '../viewportContext';

interface SamplePost {
    ar: { title: string; category: string };
    en: { title: string; category: string };
}

const SAMPLE_POSTS: SamplePost[] = [
    { ar: { title: 'دليل الهدايا لهذا الموسم', category: 'إلهام' }, en: { title: "This season's gift guide", category: 'Inspiration' } },
    { ar: { title: 'كيف تعتني بمنتجاتك', category: 'نصائح وحيل' }, en: { title: 'How to care for your items', category: 'Tips & tricks' } },
    { ar: { title: 'قصتنا', category: 'من وراء العلامة' }, en: { title: 'Our story', category: 'Behind the brand' } },
    { ar: { title: 'أفضل ٥ منتجات مبيعًا', category: 'أدلة الشراء' }, en: { title: 'Top 5 bestsellers', category: 'Buying guides' } },
    { ar: { title: 'إطلاق المجموعة الجديدة', category: 'أخبار' }, en: { title: 'New collection launch', category: 'News' } },
    { ar: { title: 'رأي عميل: تجربة رائعة', category: 'قصص عملائنا' }, en: { title: 'Customer story: a great experience', category: 'Customer stories' } },
];

interface BlogPostsGridProps {
    locale: string;
    title: { ar?: string; en?: string };
    source: string;
    columns: number;
    mobileColumns: number;
    limit: number;
}

const COL_CLASS: Record<number, string> = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
};

export function BlogPostsGrid(props: BlogPostsGridProps) {
    const viewport = useViewport();
    const title = loc(props.title, props.locale);
    const count = Math.min(props.limit || 3, SAMPLE_POSTS.length);
    const desktop = Math.max(1, Math.min(4, props.columns ?? 3));
    const mobileCols = Math.max(1, Math.min(2, props.mobileColumns ?? 1));
    const cols = viewport === 'mobile' ? mobileCols : desktop;
    const posts = SAMPLE_POSTS.slice(0, count);

    return (
        <div>
            {title && <h2 className="mb-6 text-xl font-bold text-neutral-900 sm:text-2xl">{title}</h2>}
            <div className={`grid ${COL_CLASS[cols] ?? 'grid-cols-3'} gap-4`}>
                {posts.map((p, i) => {
                    const post = props.locale === 'ar' ? p.ar : p.en;
                    return (
                        <a
                            key={i}
                            href="#"
                            onClick={(e) => e.preventDefault()}
                            className="group overflow-hidden rounded-lg border border-neutral-200 bg-white transition hover:shadow-sm"
                        >
                            <div className="aspect-[16/10] bg-neutral-100" />
                            <div className="space-y-1.5 p-4">
                                <span className="text-[11px] font-medium text-primary-600">{post.category}</span>
                                <h3 className="text-sm font-semibold leading-snug text-neutral-900">{post.title}</h3>
                            </div>
                        </a>
                    );
                })}
            </div>
        </div>
    );
}
