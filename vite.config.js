import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: 'public_html/build',
        publicDir: 'public_html/'
    },

  
  
    plugins: [

        laravel({
          
            input: [
                'resources/css/app.css',
                'resources/css/filament.css',
                 'resources/js/app.js'
                ],
            refresh: true,
            publicDir: 'public_html/'
           
        }),
    ],
});
