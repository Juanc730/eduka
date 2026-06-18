const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost/eduka',
    specPattern: 'cypress/e2e/**/*.cy.js',
    setupNodeEvents(on, config) {},
  },
});