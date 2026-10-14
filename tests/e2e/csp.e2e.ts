import { expect, test } from '@playwright/test'

const REPORT = {
  'csp-report': {
    'blocked-uri': 'https://cdn.exemple.com/lib.js',
    'effective-directive': 'script-src-elem',
    'document-uri': 'http://localhost/contact',
  },
}

test.describe('CSP en mode Report-Only', () => {
  test('pose l’en-tête avec l’API et la route de rapport', async ({ request }) => {
    const response = await request.get('/')
    const csp = response.headers()['content-security-policy-report-only'] ?? ''

    expect(csp).toContain("default-src 'self'")
    // L'API vient de API_URL du conteneur PHP, que le conteneur Playwright ne reçoit pas
    expect(csp).toMatch(/connect-src 'self' http:\/\/localhost:\d+;/)
    expect(csp).toContain('report-uri /api/csp-report')
    expect(response.headers()['content-security-policy']).toBeUndefined()
  })

  test('la route accepte un rapport (204)', async ({ request }) => {
    const response = await request.post('/api/csp-report', {
      data: JSON.stringify(REPORT),
      headers: { 'content-type': 'application/csp-report' },
    })
    expect(response.status()).toBe(204)
  })

  test('la route refuse un JSON invalide (400)', async ({ request }) => {
    const response = await request.post('/api/csp-report', {
      data: '{"csp-report":',
      headers: { 'content-type': 'application/csp-report' },
    })
    expect(response.status()).toBe(400)
  })

  test('la route refuse un corps trop long (413)', async ({ request }) => {
    const response = await request.post('/api/csp-report', {
      data: 'a'.repeat(10_001),
      headers: { 'content-type': 'application/csp-report' },
    })
    expect(response.status()).toBe(413)
  })
})
