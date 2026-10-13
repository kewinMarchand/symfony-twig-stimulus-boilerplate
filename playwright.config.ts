import { defineConfig, devices } from '@playwright/test'

// 127.0.0.1 et non localhost : Chromium essaie ::1 d’abord, où un autre service de la machine peut répondre sur le même port.
const BASE_URL = `http://127.0.0.1:${process.env.HTTP_PORT ?? 8095}`

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
