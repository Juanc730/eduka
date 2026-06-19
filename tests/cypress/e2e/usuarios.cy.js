describe('Módulo de Gestión de Usuarios', () => {

  beforeEach(() => {
    cy.clearLocalStorage();
    cy.visit('/frontend/pages/login.html');
    cy.get('#email').type('admin@eduka.com');
    cy.get('#password').type('password123');
    cy.get('#btn-login').click();
    cy.url().should('include', 'dashboard.html');
  });

  it('muestra la tarjeta de Usuarios en el dashboard para el admin', () => {
    cy.contains('Usuarios').should('be.visible');
  });

  it('navega a la gestión de usuarios y muestra la tabla', () => {
    cy.contains('Usuarios').click();
    cy.url().should('include', 'usuarios.html');
    cy.get('.tabla').should('be.visible');
    cy.get('#tabla-usuarios tr').should('have.length.greaterThan', 0);
  });

  it('permite buscar un usuario por nombre', () => {
    cy.visit('/frontend/pages/usuarios.html');
    cy.get('#buscar').type('Admin');
    cy.get('#form-buscar').submit();
    cy.get('#contador-resultados').should('be.visible');
    cy.get('#tabla-usuarios').should('contain', 'Admin');
  });

  it('abre el modal al hacer clic en Nuevo Usuario', () => {
    cy.visit('/frontend/pages/usuarios.html');
    cy.get('#btn-nuevo').click();
    cy.get('#modal-usuario').should('be.visible');
    cy.get('#modal-titulo').should('contain', 'Nuevo Usuario');
  });

  it('cierra el modal al hacer clic en Cancelar', () => {
    cy.visit('/frontend/pages/usuarios.html');
    cy.get('#btn-nuevo').click();
    cy.get('#modal-usuario').should('be.visible');
    cy.get('#btn-cancelar').click();
    cy.get('#modal-usuario').should('not.be.visible');
  });

  it('muestra los requisitos de contraseña al escribir en el modal de creación', () => {
    cy.visit('/frontend/pages/usuarios.html');
    cy.get('#btn-nuevo').click();
    cy.get('#usuario-password').type('Eduka2026@');
    cy.get('#req-len').should('have.class', 'req-ok');
    cy.get('#req-may').should('have.class', 'req-ok');
  });

  it('crea un nuevo usuario exitosamente', () => {
    const email = `cypress${Date.now()}@eduka.com`;

    cy.visit('/frontend/pages/usuarios.html');
    cy.get('#btn-nuevo').click();
    cy.get('#usuario-nombre').type('Usuario');
    cy.get('#usuario-apellido').type('Cypress');
    cy.get('#usuario-email').type(email);
    cy.get('#usuario-password').type('Eduka2026@');
    cy.get('#usuario-confirm').type('Eduka2026@');
    cy.get('#usuario-rol').select('2');
    cy.get('#form-usuario').submit();

    cy.get('#modal-usuario').should('not.be.visible');
    cy.get('#mensaje-success').should('be.visible').and('contain', 'creado');

    // Verificamos que existe usando el buscador, que sí trae el resultado sin importar la página
    cy.get('#buscar').type(email);
    cy.get('#form-buscar').submit();
    cy.get('#tabla-usuarios').should('contain', email);
  });

  it('muestra error al crear usuario con correo ya existente', () => {
    cy.visit('/frontend/pages/usuarios.html');
    cy.get('#btn-nuevo').click();
    cy.get('#usuario-nombre').type('Otro');
    cy.get('#usuario-apellido').type('Admin');
    cy.get('#usuario-email').type('admin@eduka.com');
    cy.get('#usuario-password').type('Eduka2026@');
    cy.get('#usuario-confirm').type('Eduka2026@');
    cy.get('#usuario-rol').select('1');
    cy.get('#form-usuario').submit();

    cy.get('#modal-error').scrollIntoView().should('be.visible').and('contain', 'registrado');
  });

  it('abre el modal de edición con los datos precargados', () => {
    cy.visit('/frontend/pages/usuarios.html');
    cy.get('.btn-editar').first().click();
    cy.get('#modal-usuario').should('be.visible');
    cy.get('#modal-titulo').should('contain', 'Editar Usuario');
    cy.get('#usuario-nombre').should('not.have.value', '');
  });

  it('permite activar y desactivar un usuario desde la tabla', () => {
    cy.visit('/frontend/pages/usuarios.html');

    cy.on('window:confirm', () => true);

    // Buscamos específicamente por "estudiante" para encontrar una fila segura de modificar
    cy.get('#buscar').type('estudiante');
    cy.get('#form-buscar').submit();

    cy.get('#tabla-usuarios tr').then($filas => {
      const filaValida = [...$filas].find(fila => fila.innerText.includes('ESTUDIANTE'));

      if (!filaValida) {
        throw new Error('No se encontró ninguna fila de estudiante para la prueba.');
      }

      const id = filaValida.querySelector('.btn-toggle').dataset.id;
      cy.get(`.btn-toggle[data-id="${id}"]`).click();
    });

    cy.get('#mensaje-success').should('be.visible');
  });

});