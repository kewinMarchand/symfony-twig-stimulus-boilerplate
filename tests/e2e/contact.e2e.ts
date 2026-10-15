import { expect, test } from '@playwright/test'

test.describe('Contact', () => {
  test('signale chaque champ obligatoire dans son libellé', async ({ page }) => {
    await page.goto('/contact')

    for (const field of ['contact-name', 'contact-email', 'contact-message']) {
      const input = page.getByTestId(field)
      await expect(input).toHaveAttribute('required', 'required')
      await expect(input).toHaveAccessibleName(/\(obligatoire\)$/)
    }
  })

  test('affiche les erreurs de validation quand le formulaire est vide', async ({ page }) => {
    await page.goto('/contact')
    await page.getByTestId('contact-submit').click()

    const name = page.getByTestId('contact-name')
    await expect(name).toHaveAttribute('aria-invalid', 'true')
    await expect(name).toHaveAccessibleDescription(/2 caractères minimum/)
    await expect(name).toBeFocused()
    await expect(page.getByTestId('contact-success')).toHaveCount(0)
  })

  test('confirme l’envoi quand le formulaire est valide', async ({ page }) => {
    await page.goto('/contact')
    await page.getByTestId('contact-name').fill('Ada')
    await page.getByTestId('contact-email').fill('ada@exemple.fr')
    await page.getByTestId('contact-message').fill('Bonjour, ceci est un message.')
    await page.getByTestId('contact-submit').click()

    await expect(page.getByTestId('contact-success')).toBeVisible()
    await expect(page.getByTestId('contact-name')).toHaveValue('')
    await expect(page.getByTestId('contact-name')).not.toHaveAttribute('aria-invalid')
  })
})

test.describe('Contact sans JavaScript', () => {
  test.use({ javaScriptEnabled: false })

  test('le formulaire s’envoie par une soumission classique', async ({ page }) => {
    await page.goto('/contact')
    await page.getByTestId('contact-name').fill('Ada')
    await page.getByTestId('contact-email').fill('ada@exemple.fr')
    await page.getByTestId('contact-message').fill('Bonjour, ceci est un message.')
    await page.getByTestId('contact-submit').click()

    await expect(page).toHaveURL(/\/contact$/)
    await expect(page.getByTestId('contact-success')).toBeVisible()
  })
})
