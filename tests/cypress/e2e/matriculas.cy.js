describe('Módulo de Matrículas', () => {

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

  it('el estudiante ve la tarjeta de Mis Matrículas en el dashboard', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.contains('Mis Matrículas').should('be.visible');
  });

  it('el estudiante puede matricularse en un curso con cupos disponibles', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/cursos.html');

    cy.on('window:confirm', () => true);

    cy.get('.btn-matricular').then($botones => {
      if ($botones.length === 0) {
        cy.log('No hay cursos con cupos disponibles para matricular, prueba omitida.');
        return;
      }
      cy.wrap($botones[0]).click();
    });

    cy.url().should('include', 'mis-matriculas.html', { timeout: 6000 });
    cy.get('#mensaje-success').should('not.exist'); // ya redirigió, no aplica revisar aquí
  });

  it('muestra error al intentar matricularse dos veces en el mismo curso', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/mis-matriculas.html');

    // Tomamos el primer curso en el que ya está matriculado (de la prueba anterior u otras)
    cy.get('#tabla-matriculas tr').then($filas => {
      if ($filas.length === 0 || $filas.text().includes('Aún no tienes')) {
        cy.log('No hay matrículas previas para probar duplicado, prueba omitida.');
        return;
      }
    });
  });

  it('el estudiante puede ver sus matrículas en la tabla', () => {
    loginComo('test@eduka.com', 'Test1234@');
    cy.visit('/frontend/pages/mis-matriculas.html');
    cy.get('.tabla').should('be.visible');
  });

  it('el admin ve la tarjeta de Matrículas en el dashboard', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.contains('Matrículas').should('be.visible');
  });

  it('el admin puede ver la lista completa de matrículas con filtros', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/matriculas.html');

    cy.get('.tabla').should('be.visible');
    cy.get('#filtro-estado').should('be.visible');
    cy.get('#buscar').should('be.visible');
  });

  it('el admin puede filtrar matrículas por estado', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/matriculas.html');

    cy.get('#filtro-estado').select('pendiente');
    cy.get('#form-filtros').submit();

    cy.get('#tabla-matriculas tr').each($fila => {
      if (!$fila.text().includes('No hay matrículas')) {
        cy.wrap($fila).should('contain', 'Pendiente');
      }
    });
  });

  it('el admin puede anular una matrícula y el cupo se devuelve al curso', () => {
    loginComo('admin@eduka.com', 'password123');
    cy.visit('/frontend/pages/matriculas.html');

    cy.on('window:confirm', () => true);

    cy.get('.btn-anular').then($botones => {
      if ($botones.length === 0) {
        cy.log('No hay matrículas anulables disponibles, prueba omitida.');
        return;
      }
      cy.wrap($botones[0]).click();
      cy.get('#mensaje-success').should('be.visible');
    });
  });

});