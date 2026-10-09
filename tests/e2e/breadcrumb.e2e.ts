import { expect, test } from '@playwright/test'

const breadcrumbJsonLd = async (page: import('@playwright/test').Page) => {
  const scripts = await page.locator('script[type="application/ld+json"]').allTextContents()
  return scripts.map((script) => JSON.parse(script)).find((data) => data['@type'] === 'BreadcrumbList')
}

test.describe('Fil d’Ariane', () => {
  test('est absent de l’accueil', async ({ page }) => {
    await page.goto('/')

    await expect(page.getByTestId('layout-breadcrumb')).toHaveCount(0)
    expect(await breadcrumbJsonLd(page)).toBeUndefined()
  })

  test('est présent sur une page interne, dernier élément en aria-current', async ({ page }) => {
    await page.goto('/catalogue/plantes-interieur/feuillages')

    const breadcrumb = page.getByTestId('layout-breadcrumb')
    await expect(breadcrumb.getByRole('listitem')).toHaveCount(4)
    await expect(breadcrumb.locator('[aria-current="page"]')).toHaveText('Feuillages')
    await expect(breadcrumb.getByRole('link', { name: 'Accueil' })).toHaveAttribute('href', '/')

    const jsonLd = await breadcrumbJsonLd(page)
    expect(jsonLd['@type']).toBe('BreadcrumbList')
    expect(jsonLd.itemListElement).toHaveLength(4)
    expect(jsonLd.itemListElement[3]).toMatchObject({ position: 4, name: 'Feuillages' })
  })
})
