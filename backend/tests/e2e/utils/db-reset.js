import { execSync } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Reset backend database to a fresh state with rich demo data (Branches, Items, Shifts, Customers, etc.)
 */
export function resetFreshDatabase() {
    const backendPath = path.resolve(__dirname, '../../../backend');
    console.log('🔄 Resetting backend database to clean fresh state with rich seed data...');
    try {
        execSync('php artisan migrate:fresh --seed', {
            cwd: backendPath,
            stdio: 'inherit',
            timeout: 60000,
        });
        console.log('✅ Fresh database seeded successfully with all branches and items!');
    } catch (e) {
        console.error('⚠️ Database reset error:', e.message);
    }
}
