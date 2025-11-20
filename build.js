import esbuild from 'esbuild';
//import { createRequire } from 'node:module';
import { copyFile, mkdir } from 'node:fs/promises';
import { dirname } from 'node:path';

//const require = createRequire(import.meta.url);

async function copyCSS() {
    const cssFiles = [
        { 
            from: 'resources/css/admin.css',
            to: 'assets/css/admin.css'
        },
        { 
            from: 'node_modules/@uppy/core/dist/style.min.css',
            to: 'assets/css/uppy-core.min.css'
        },
        { 
            from: 'node_modules/@uppy/dashboard/dist/style.min.css',
            to: 'assets/css/uppy-dashboard.min.css'
        }
    ];

    for (const { from, to } of cssFiles) {
        await mkdir(dirname(to), { recursive: true });
        await copyFile(from, to);
        console.log(`✓ Copied ${from} -> ${to}`);
    }
}

async function build() {
    try {
        // Copy CSS files
        await copyCSS();
        
        // Build JavaScript
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
        
        console.log('✓ Build completed successfully');
    } catch (error) {
        console.error('✗ Build failed:', error);
        throw error; // Throw instead of process.exit(1)
    }
}

build();
