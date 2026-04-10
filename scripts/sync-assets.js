const { cp, mkdir, writeFile } = require('fs/promises');
const { existsSync } = require('fs');
const { dirname, join } = require('path');

const projectRoot = join(__dirname, '..');
const publicAssets = join(projectRoot, 'public', 'assets');
const vendorFiles = [
  ['node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/assets/vendor/bootstrap/css/bootstrap.min.css'],
  ['node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js'],
  ['node_modules/bootstrap-icons/font/bootstrap-icons.css', 'public/assets/vendor/bootstrap-icons/bootstrap-icons.css'],
  ['node_modules/bootstrap-icons/font/fonts', 'public/assets/vendor/bootstrap-icons/fonts'],
  ['node_modules/aos/dist/aos.css', 'public/assets/vendor/aos/aos.css'],
  ['node_modules/aos/dist/aos.js', 'public/assets/vendor/aos/aos.js'],
  ['node_modules/swiper/swiper-bundle.min.css', 'public/assets/vendor/swiper/swiper-bundle.min.css'],
  ['node_modules/swiper/swiper-bundle.min.js', 'public/assets/vendor/swiper/swiper-bundle.min.js'],
  ['node_modules/glightbox/dist/css/glightbox.min.css', 'public/assets/vendor/glightbox/css/glightbox.min.css'],
  ['node_modules/glightbox/dist/js/glightbox.min.js', 'public/assets/vendor/glightbox/js/glightbox.min.js'],
];

async function copyPath(sourceRel, destRel) {
  const source = join(projectRoot, sourceRel);
  const dest = join(projectRoot, destRel);
  await mkdir(dirname(dest), { recursive: true });
  await cp(source, dest, { recursive: true, force: true });
}

async function syncAssets() {
  console.log('Syncing assets from assets/ to public/assets/...');
  await mkdir(publicAssets, { recursive: true });
  await copyPath('assets', 'public/assets');

  console.log('Copying frontend vendor libraries from node_modules/...');
  for (const [src, dest] of vendorFiles) {
    const source = join(projectRoot, src);
    if (!existsSync(source)) {
      console.warn(`Warning: vendor source not found: ${src}`);
      continue;
    }
    await copyPath(src, dest);
  }

  const phpEmailFormSource = join(projectRoot, 'assets/vendor/php-email-form/validate.js');
  const phpEmailFormDest = join(projectRoot, 'public/assets/vendor/php-email-form/validate.js');
  await mkdir(dirname(phpEmailFormDest), { recursive: true });
  if (existsSync(phpEmailFormSource)) {
    await cp(phpEmailFormSource, phpEmailFormDest, { force: true });
  } else {
    await writeFile(phpEmailFormDest, '// Placeholder for php-email-form validation.\n', 'utf8');
  }

  console.log('Asset sync complete.');
}

syncAssets().catch(error => {
  console.error('Asset sync failed:', error);
  process.exit(1);
});
