describe('Módulo de Pagos', () => {

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

  it('el admin puede cargar un código Yape nuevo', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cargar-yape.html');

    const codigo = `YPE-CY-${Date.now()}`;

    cy.get('#codigo').type(codigo);
    cy.get('#monto').type('150.00');
    cy.get('#nombre_pagador').type('Estudiante Cypress');
    cy.get('#telefono_pagador').type('987654321');
    cy.get('#fecha_operacion').type('2026-06-20T10:00');
    cy.get('#form-yape').submit();

    cy.get('#mensaje-success').should('be.visible');
    cy.get('#tabla-operaciones').should('contain', codigo);
  });

  it('el admin ve el estado "Disponible" en un código recién creado', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cargar-yape.html');
    cy.get('#tabla-operaciones').should('contain', 'Disponible');
  });

  it('el estudiante ve la tarjeta de Mis Pagos en el dashboard', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.contains('Mis Pagos').should('be.visible');
  });

  it('el estudiante puede ver la página de Mis Pagos', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/mis-pagos.html');
    cy.get('.tabla').should('be.visible');
  });

  it('el estudiante puede pagar con un código Yape válido y se confirma automáticamente', () => {
    const codigo = `YPE-FLOW-${Date.now()}`;
    const monto = '180.00';

    // Paso 1: el admin carga el código
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cargar-yape.html');
    cy.get('#codigo').type(codigo);
    cy.get('#monto').type(monto);
    cy.get('#fecha_operacion').type('2026-06-20T10:00');
    cy.get('#form-yape').submit();
    cy.get('#mensaje-success').should('be.visible');

    // Paso 2: cerrar sesión y entrar como estudiante
    cy.get('#btn-logout').click();
    cy.on('window:confirm', () => true);

    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/cursos.html');

    cy.on('window:confirm', () => true);

    // Paso 3: matricularse en un curso con cupos
    cy.get('.btn-matricular').then($botones => {
      if ($botones.length === 0) {
        cy.log('No hay cursos disponibles para matricular, prueba omitida.');
        return;
      }
      cy.wrap($botones[0]).click();
    });

    cy.url().should('include', 'mis-matriculas.html');

    // Paso 4: ir a registrar el pago de la matrícula más reciente
    cy.get('#tabla-matriculas tr').first().find('a').contains('Registrar pago').click();

    cy.url().should('include', 'pagar.html');

    cy.get('#codigo-yape').type(codigo);
    cy.get('#monto-codigo').type(monto);
    cy.get('#form-codigo').submit();

    cy.get('#mensaje-exito', { timeout: 6000 }).should('be.visible');
  });

  it('muestra error al usar un código Yape inexistente', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/mis-matriculas.html');

    cy.get('#tabla-matriculas tr').then($filas => {
      const filaConPago = [...$filas].find(f => f.innerText.includes('Registrar pago') || f.innerText.includes('Reintentar'));
      if (!filaConPago) {
        cy.log('No hay matrículas pendientes de pago disponibles, prueba omitida.');
        return;
      }

      cy.wrap(filaConPago).find('a').click();
      cy.url().should('include', 'pagar.html');

      cy.get('#codigo-yape').type('CODIGO-QUE-NO-EXISTE-999');
      cy.get('#monto-codigo').type('100.00');
      cy.get('#form-codigo').submit();

      cy.get('#mensaje-error').should('be.visible').and('contain', 'inválido');
    });
  });

  it('el admin ve la tarjeta de Pagos en el dashboard', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.contains('Pagos').should('be.visible');
  });

  it('el admin puede ver la lista de pagos con buscador', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/admin-pagos.html');

    cy.get('.tabla').should('be.visible');
    cy.get('#buscar').should('be.visible');
  });

});