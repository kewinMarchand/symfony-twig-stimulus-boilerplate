import { defineConfig, devices } from '@playwright/test'

const BASE_URL = `http://localhost:${process.env.HTTP_PORT ?? 8095}`

export default defineConfig({
  testMatch: /.*\.(e2e|a11y)\.ts/,
  fullyParallel: true,
  workers: process.env.CI ? 2 : 4,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
  use: {
    baseURL: BASE_URL,
    locale: 'fr-FR',
    testIdAttribute: 'data-testid',
    trace: 'on-first-retry',
  },
  projects: [
    { name: 'desktop', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile', use: { ...devices['Pixel 7'] } },
  ],
})
