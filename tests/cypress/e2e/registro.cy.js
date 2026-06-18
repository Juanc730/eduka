describe('Módulo de Registro', () => {

  it('muestra el formulario de registro correctamente', () => {
    cy.visit('/frontend/pages/registro.html');
    cy.contains('Crear Cuenta').should('be.visible');
  });

  it('muestra los requisitos de contraseña en tiempo real', () => {
    cy.visit('/frontend/pages/registro.html');
    cy.get('#password').type('a');
    cy.get('#req-len').should('have.class', 'req-fail');

    cy.get('#password').clear().type('Eduka2026@');
    cy.get('#req-len').should('have.class', 'req-ok');
    cy.get('#req-may').should('have.class', 'req-ok');
    cy.get('#req-min').should('have.class', 'req-ok');
    cy.get('#req-num').should('have.class', 'req-ok');
    cy.get('#req-esp').should('have.class', 'req-ok');
  });

  it('muestra mensaje cuando las contraseñas no coinciden', () => {
    cy.visit('/frontend/pages/registro.html');
    cy.get('#password').type('Eduka2026@');
    cy.get('#confirm_password').type('Diferente2026@');
    cy.get('#msg-confirm').should('contain', 'no coinciden');
  });

  it('muestra mensaje cuando las contraseñas sí coinciden', () => {
    cy.visit('/frontend/pages/registro.html');
    cy.get('#password').type('Eduka2026@');
    cy.get('#confirm_password').type('Eduka2026@');
    cy.get('#msg-confirm').should('contain', 'coinciden');
  });

  it('registra un nuevo usuario exitosamente', () => {
    const email = `test${Date.now()}@eduka.com`;

    cy.visit('/frontend/pages/registro.html');
    cy.get('#nombre').type('Usuario');
    cy.get('#apellido').type('De Prueba');
    cy.get('#email').type(email);
    cy.get('#password').type('Eduka2026@');
    cy.get('#confirm_password').type('Eduka2026@');
    cy.get('#form-registro').submit();

    cy.get('#mensaje-success').should('be.visible');
    cy.url('include', 'login.html', { timeout: 5000 });
  });

  it('muestra error al registrar con un correo ya existente', () => {
    cy.visit('/frontend/pages/registro.html');
    cy.get('#nombre').type('Otro');
    cy.get('#apellido').type('Usuario');
    cy.get('#email').type('admin@eduka.com');
    cy.get('#password').type('Eduka2026@');
    cy.get('#confirm_password').type('Eduka2026@');
    cy.get('#form-registro').submit();

    cy.get('#mensaje-error').should('contain', 'registrado');
  });

});