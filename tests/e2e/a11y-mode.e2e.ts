import { expect, test } from '@playwright/test'

test.describe('Mode accessibilité renforcée', () => {
  test('est désactivé par défaut', async ({ page }) => {
    await page.goto('/')

    await expect(page.locator('html')).not.toHaveAttribute('data-a11y-mode')
    await expect(page.getByTestId('a11y-mode-toggle')).toHaveAttribute('aria-pressed', 'false')
  })

  test('s’active, persiste après rechargement, puis se désactive', async ({ page }) => {
    await page.goto('/')
    const toggle = page.getByTestId('a11y-mode-toggle')
    const html = page.locator('html')

    await toggle.click()
    await expect(html).toHaveAttribute('data-a11y-mode', 'enhanced')
    await expect(toggle).toHaveAttribute('aria-pressed', 'true')

    await page.reload()
    await expect(html).toHaveAttribute('data-a11y-mode', 'enhanced')
    await expect(page.getByTestId('a11y-mode-toggle')).toHaveAttribute('aria-pressed', 'true')

    await page.getByTestId('a11y-mode-toggle').click()
    await expect(html).not.toHaveAttribute('data-a11y-mode')
    await expect(page.getByTestId('a11y-mode-toggle')).toHaveAttribute('aria-pressed', 'false')
    expect(await page.evaluate(() => window.localStorage.getItem('a11y-mode'))).toBeNull()
  })
})
