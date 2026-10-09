import { expect, test } from '@playwright/test'

test.describe('Serveur de développement @dev', () => {
  test('la page 500 est neutre et sans détail technique @dev', async ({ page }) => {
    const response = await page.goto('/_error/500')

    expect(response?.status()).toBe(500)
    await expect(page.getByRole('heading', { level: 1, name: 'Une erreur est survenue' })).toBeVisible()
    await expect(page.getByTestId('error-retry')).toBeVisible()
    await expect(page.getByTestId('error-home-link')).toBeVisible()
    const text = await page.locator('main').innerText()
    expect(text).not.toMatch(/error|exception|stack|trace/i)
  })

  test('la charte graphique présente ses dix sections @dev', async ({ page }) => {
    const response = await page.goto('/charte-graphique')

    expect(response?.status()).toBe(200)
    await expect(page.locator('main section > h2')).toHaveCount(10)
    await expect(page.getByTestId('styleguide-colors')).toContainText('--color-primary')
    await expect(page.getByTestId('styleguide-colors')).toContainText(/AA|AAA/)
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'noindex, nofollow')
  })
})
