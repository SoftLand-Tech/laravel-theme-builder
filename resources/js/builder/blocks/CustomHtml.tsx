import React from 'react';

interface CustomHtmlProps {
    html: string;
}

export function CustomHtml(props: CustomHtmlProps) {
    const html = props.html ?? '';
    if (html === '') return null;
    // The block's props are sanitized server-side — safe markup only
    // (scripts, inline handlers, and javascript: URLs are stripped).
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}
