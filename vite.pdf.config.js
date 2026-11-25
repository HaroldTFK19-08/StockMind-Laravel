import { readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import postcss from 'postcss';

/**
 * DomPDF no interpreta @layer ni @supports, así que el CSS compilado con
 * Tailwind se deja en reglas planas antes de guardarlo en public/css/pdf.css.
 */
function cssPlanoParaDompdf() {
    const desenvolver = (root) => {
        root.walkAtRules(/^(layer|supports)$/, (regla) => {
            desenvolver(regla);
            regla.replaceWith(regla.nodes);
        });
    };

    return {
        name: 'css-plano-para-dompdf',
        enforce: 'post',
        closeBundle() {
            const archivoCss = resolve('public/css/pdf.css');
            const root = postcss.parse(readFileSync(archivoCss, 'utf8'));

            desenvolver(root);

            writeFileSync(archivoCss, root.toString());
        },
    };
}

export default defineConfig({
    plugins: [tailwindcss(), cssPlanoParaDompdf()],
    build: {
        outDir: 'public/css',
        emptyOutDir: false,
        rollupOptions: {
            input: 'resources/css/pdf.css',
            output: {
                assetFileNames: 'pdf.css',
            },
        },
    },
});
