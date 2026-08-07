import React from 'react';
import { loc } from './_shared';
import { useT } from '../i18n';
import { SAMPLE_PRODUCTS } from './ProductCardPreview';

interface ProductsFeaturedProps {
    locale: string;
    title: { ar?: string; en?: string };
    productId: number | null;
}

export function ProductsFeatured(props: ProductsFeaturedProps) {
    const t = useT();
    const title = loc(props.title, props.locale);
    const product = props.productId ? SAMPLE_PRODUCTS[0] : null;

    if (!product) {
        return (
            <div>
                {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
                <div className="mx-auto max-w-lg rounded-lg border border-dashed border-neutral-300 bg-neutral-50 px-6 py-12 text-center text-sm text-neutral-500">
                    {t('No product selected')}
                </div>
            </div>
        );
    }

    const name = loc(product.name, props.locale);

    return (
        <div>
            {title && <h2 className="mb-6 text-center text-2xl font-bold text-neutral-900">{title}</h2>}
            <div className="mx-auto max-w-4xl rounded-lg border border-neutral-200 bg-white p-8">
                <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        {product.image ? (
                            <img src={product.image} alt={name} className="aspect-square w-full rounded-lg object-cover" />
                        ) : (
                            <div className="flex aspect-square w-full items-center justify-center rounded-lg bg-neutral-100 text-neutral-400">
                                {props.locale === 'ar' ? 'لا صورة' : 'No image'}
                            </div>
                        )}
                    </div>
                    <div className="flex flex-col justify-center">
                        <h3 className="mb-2 text-2xl font-bold text-neutral-900">{name}</h3>
                        <p className="mb-4 text-sm text-neutral-500">{product.price}</p>
                        <a
                            href="#"
                            onClick={(e) => e.preventDefault()}
                            className="inline-block w-fit rounded-full bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                        >
                            {t('View product')}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    );
}
