import esbuild from 'esbuild';
import { copyFile, mkdir } from 'node:fs/promises';
import path from 'node:path';

async function processCSS() {
    // Process custom CSS with esbuild (minify)
    await esbuild.build({
        entryPoints: ['resources/css/admin.css'],
        outfile: 'assets/css/admin.min.css',
        minify: true,
        loader: { '.css': 'css' }
    });
    console.log('✓ Minified resources/css/admin.css -> assets/css/admin.min.css');

    // Copy vendor CSS files (already minified)
    const vendorCssFiles = [
        { 
            from: 'node_modules/@uppy/core/dist/style.min.css',
            to: 'assets/css/uppy-core.min.css'
        },
        { 
            from: 'node_modules/@uppy/dashboard/dist/style.min.css',
            to: 'assets/css/uppy-dashboard.min.css'
        }
    ];

    for (const { from, to } of vendorCssFiles) {
        await mkdir(path.dirname(to), { recursive: true });
        await copyFile(from, to);
        console.log(`✓ Copied ${from} -> ${to}`);
    }
}

async function processJS() {
    await esbuild.build({
        entryPoints: ['resources/js/admin.js'],
        bundle: true,
        outfile: 'assets/js/admin.min.js',
        format: 'iife',
        minify: true,
        sourcemap: false,
        external: ['jquery'],
        define: {
            'process.env.NODE_ENV': '"production"'
        }
    });
    console.log('✓ Built resources/js/admin.js -> assets/js/admin.min.js');
}

try {
    await processCSS();
    await processJS();
    
    console.log('✓ Build completed successfully');
} catch (error) {
    console.error('✗ Build failed:', error);
    throw error;
}
