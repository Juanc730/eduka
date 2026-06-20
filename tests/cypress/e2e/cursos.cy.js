describe('Módulo de Cursos', () => {

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

  it('el admin ve la tarjeta de Gestión de Cursos en el dashboard', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.contains('Gestión de Cursos').should('be.visible');
  });

  it('el admin puede navegar a cursos y ver la lista', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.contains('Gestión de Cursos').click();
    cy.url().should('include', 'cursos.html');
    cy.get('#btn-nuevo').should('be.visible');
  });

  it('el admin puede crear un nuevo curso exitosamente', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cursos.html');

    const nombreCurso = `Curso Cypress ${Date.now()}`;

    cy.get('#btn-nuevo').click();
    cy.get('#modal-curso').should('be.visible');
    cy.get('#curso-nombre').type(nombreCurso);
    cy.get('#curso-descripcion').type('Descripción de prueba generada por Cypress');
    cy.get('#curso-cupos').type('25');
    cy.get('#curso-horario').type('Lunes y Viernes 6pm-8pm');
    cy.get('#form-curso').submit();

    cy.get('#modal-curso').should('not.be.visible');
    cy.get('#mensaje-success').should('be.visible');
    cy.get('#cards-cursos').should('contain', nombreCurso);
  });

  it('muestra error al crear un curso sin completar campos obligatorios', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cursos.html');

    cy.get('#btn-nuevo').click();
    // Dejamos el nombre vacío a propósito; el navegador debería bloquear el submit por "required"
    cy.get('#curso-cupos').type('10');
    cy.get('#curso-horario').type('Lunes');
    cy.get('#form-curso').submit();

    // El modal debe seguir visible porque el campo requerido vacío impide el envío
    cy.get('#modal-curso').should('be.visible');
  });

  it('el admin puede editar un curso existente', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cursos.html');

    cy.get('.btn-editar').first().click();
    cy.get('#modal-curso').should('be.visible');
    cy.get('#modal-titulo').should('contain', 'Editar Curso');

    cy.get('#curso-horario').clear().type('Horario Actualizado por Cypress');
    cy.get('#form-curso').submit();

    cy.get('#mensaje-success').should('be.visible');
    cy.get('#cards-cursos').should('contain', 'Horario Actualizado por Cypress');
  });

  it('el admin puede eliminar un curso', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/cursos.html');

    const nombreCurso = `Curso a Eliminar ${Date.now()}`;

    // Creamos un curso específico para esta prueba, garantizando que exista algo seguro de eliminar
    cy.get('#btn-nuevo').click();
    cy.get('#curso-nombre').type(nombreCurso);
    cy.get('#curso-cupos').type('5');
    cy.get('#curso-horario').type('Sábados');
    cy.get('#form-curso').submit();
    cy.get('#mensaje-success').should('be.visible');

    cy.on('window:confirm', () => true);

    cy.contains('.curso-card', nombreCurso).find('.btn-eliminar').click();

    cy.get('#mensaje-success').should('be.visible');
    cy.get('#cards-cursos').should('not.contain', nombreCurso);
  });

  it('el estudiante ve los cursos pero no puede editarlos ni eliminarlos', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/cursos.html');

    cy.get('#btn-nuevo').should('not.be.visible');
    cy.get('.btn-editar').should('not.exist');
    cy.get('.btn-eliminar').should('not.exist');
  });

  it('el estudiante ve el indicador de cupos en cada curso', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/cursos.html');

    cy.get('.cupo-indicador').should('have.length.greaterThan', 0);
  });

});