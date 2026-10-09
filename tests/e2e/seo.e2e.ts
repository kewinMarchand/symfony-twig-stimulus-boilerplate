import { expect, test } from '@playwright/test'

import { ROUTES } from './routes'

for (const route of ROUTES) {
  test(`les métadonnées SEO de ${route} sont complètes`, async ({ page }) => {
    await page.goto(route)

    expect((await page.title()).length).toBeGreaterThan(0)

    const description = await page.locator('meta[name="description"]').getAttribute('content')
    expect(description?.length).toBeGreaterThanOrEqual(50)
    expect(description?.length).toBeLessThanOrEqual(160)

    expect(await page.locator('link[rel="canonical"]').getAttribute('href')).toMatch(/^https?:\/\//)
    for (const property of ['og:title', 'og:description', 'og:image']) {
      await expect(page.locator(`meta[property="${property}"]`)).toHaveAttribute('content', /.+/)
    }
    expect(await page.locator('meta[property="og:image"]').getAttribute('content')).toMatch(/^https?:\/\//)

    for (const script of await page.locator('script[type="application/ld+json"]').allTextContents()) {
      expect(() => JSON.parse(script)).not.toThrow()
    }
  })
}

test('chaque page a un titre unique', async ({ page }) => {
  const titles: string[] = []
  for (const route of ROUTES) {
    await page.goto(route)
    titles.push(await page.title())
  }

  expect(new Set(titles).size).toBe(ROUTES.length)
})
