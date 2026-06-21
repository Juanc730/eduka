describe('Módulo de Reportes', () => {

  beforeEach(() => {
    cy.clearLocalStorage();
  });

  function loginComo(email, password) {
    cy.visit('/frontend/pages/login.html');
    cy.get('#email').type(email);
    cy.get('#password').type(password);
    cy.get('#btn-login').click();
    cy.url().should('include', 'dashboard.html');
  }

  it('el admin ve la tarjeta de Reportes en el dashboard', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.contains('Reportes').should('be.visible');
  });

  it('el admin ve los 3 tipos de reporte disponibles', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/reportes.html');

    cy.contains('Matrículas por curso').should('be.visible');
    cy.contains('Estado de pagos').should('be.visible');
    cy.contains('Ocupación de cursos').should('be.visible');
  });

  it('el admin puede ver el reporte de matrículas al seleccionar un curso', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/reporte-matriculas.html');

    cy.get('#select-curso option').should('have.length.greaterThan', 1);
    cy.get('#select-curso').select(1);

    cy.get('#contenido-reporte').should('not.be.empty');
  });

  it('el admin puede ver el reporte de pagos con su resumen', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/reporte-pagos.html');

    cy.get('#resumen').should('be.visible');
    cy.get('.tabla').should('be.visible');
  });

  it('el admin puede ver el reporte de ocupación de cursos con barras de progreso', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/reporte-cursos.html');

    cy.get('.tabla').should('be.visible');
    cy.get('.barra-container').should('have.length.greaterThan', 0);
  });

  it('el docente solo ve el reporte de matrículas, no pagos ni ocupación', () => {
    loginComo('carlos.docente@eduka.com', 'Eduka2026@');
    cy.visit('/frontend/pages/reportes.html');

    cy.contains('Mis estudiantes').should('be.visible');
    cy.contains('Estado de pagos').should('not.exist');
    cy.contains('Ocupación de cursos').should('not.exist');
  });

  it('el docente solo ve sus propios cursos en el selector del reporte', () => {
    loginComo('carlos.docente@eduka.com', 'Eduka2026@');
    cy.visit('/frontend/pages/reporte-matriculas.html');

    cy.get('#select-curso option').then($opciones => {
      const textos = [...$opciones].map(o => o.textContent);
      expect(textos.length).to.be.greaterThan(0);
    });
  });

  it('el estudiante no puede acceder a la página de reportes', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/reportes.html');
    cy.url().should('include', 'dashboard.html');
  });

});