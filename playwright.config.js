// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright E2E Configuration for Mobile ERP
 */
export default defineConfig({
  testDir: './tests/e2e/specs',
  timeout: 30000,
  expect: {
    timeout: 6000
  },
  fullyParallel: false,
  retries: 0,
  workers: 1,
  reporter: [
    ['list'],
    ['html', { outputFolder: 'tests/e2e/reports', open: 'never' }]
  ],
  use: {
    baseURL: 'http://127.0.0.1:8080',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    bypassCSP: true,
    locale: 'ar-EG',
    timezoneId: 'Africa/Cairo',
  },

  /* Configure exclusively for Mobile Devices */
  projects: [
    {
      name: 'Mobile-Pixel-7',
      use: { 
        ...devices['Pixel 7'],
        channel: 'msedge',
        locale: 'ar-EG',
      },
    },
    {
      name: 'Mobile-iPhone-14',
      use: { 
        ...devices['iPhone 14 Pro'],
        channel: 'msedge',
        locale: 'ar-EG',
      },
    },
  ],
});
