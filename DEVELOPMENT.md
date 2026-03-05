# Asset Management and Local Development Workflow

The following is a structured workflow for local development and production deployment.

## 1. Local Development: Running Vite with HMR

Since the application and the package have independent `vite.config.js` files, you can run their dev servers separately. The `@paymentAssets` directive and `AssetHelper` class are designed to detect if the package's dev server is running by checking for a `hot` file in the package's build directory.

- **To run HMR for the Application Assets:**
  In the application root directory, run:
  ```bash
  npm run dev
  ```
- **To run HMR for the Package Assets:**
  Navigate to the package directory (`../packages/mralston/payment`) and run:
  ```bash
  npm run dev
  ```
  When the package's Vite server starts, it creates a `public/vendor/mralston/payment/build/hot` file. The application detects this file and automatically pulls assets from the package's dev server instead of the compiled manifest.

## 2. Local Development: Coding with the Package in its Main Repo

The best way to work on the package locally while it's integrated into the application is by using **Composer's "path" repository**. This creates a symbolic link (`symlink`) from your application's `vendor/mralston/payment` directory to your local package repository.

### Manual Setup (Deprecated)

Previously, you had to manually modify the application's `composer.json` to include the local package path:

```json
"repositories": [
    {
        "type": "path",
        "url": "../packages/mralston/payment",
        "options": {
            "symlink": true
        }
    }
]
```

### Automated Setup (Recommended)

You can now use the included Artisan commands to switch between development and production environments.

- **Check Current Environment:**
  ```bash
  php artisan payment:get-env
  ```
- **Switch Environment:**
  ```bash
  php artisan payment:switch-env [dev|prod|toggle]
  ```

- **Dev Mode:** Adds the `path` repository to the root `composer.json`, sets the version to `dev-main`, and runs `composer update`.
- **Prod Mode:** Removes the `path` repository, sets the version to a stable constraint (e.g., `^1.0`), and runs `composer update`.

Now, any changes you make in the package source are immediately reflected in the application's `vendor` folder due to the symlink.

## 3. Production Deployment: Asset Transition

The production flow ensures that the application doesn't need to know how to build the package; it only needs to publish the pre-built assets.

- **Package Side (Local Machine):**
  1.  Before committing changes to the package repo, run `npm run build` inside the package directory.
  2.  This generates the compiled assets in `public/vendor/mralston/payment/build` (including the `manifest.json`).
  3.  **Commit and push these compiled assets** to your package repository. This is standard for Laravel packages that provide their own frontend assets.

- **Application Side (Production Server):**
  1.  When you run `composer install` (or `composer update`), the pre-built assets are downloaded into the `vendor/mralston/payment` directory.
  2.  As part of your deployment script, run the following Artisan command to copy the assets into the application's `public` folder:
      ```bash
      php artisan vendor:publish --tag=payment-assets --force
      ```
  3.  The application's `@paymentAssets` directive (defined in `PaymentServiceProvider`) will see that no `hot` file exists and will load the assets from the published `manifest.json` using the `AssetHelper`.

## Summary of the `@paymentAssets` Logic

The logic implemented in `Mralston\Payment\Helpers\AssetHelper` handles the heavy lifting:
- **Local + Package Dev Server running:** Injects a script tag pointing to the Vite dev server (e.g., `http://localhost:5174/...`).
- **Production / No Dev Server:** Reads the published `manifest.json` and injects the versioned `<script>` and `<link>` (CSS) tags.
