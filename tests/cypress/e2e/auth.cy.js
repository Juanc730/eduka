describe('Módulo de Autenticación', () => {

  beforeEach(() => {
    cy.clearLocalStorage();
  });

  it('muestra el formulario de login correctamente', () => {
    cy.visit('/frontend/pages/login.html');
    cy.contains('Iniciar Sesión').should('be.visible');
    cy.get('#email').should('be.visible');
    cy.get('#password').should('be.visible');
  });

  it('muestra errores de validación con campos vacíos', () => {
    cy.visit('/frontend/pages/login.html');
    cy.get('#btn-login').click();
    cy.get('#error-email').should('contain', 'obligatorio');
    cy.get('#error-password').should('contain', 'obligatoria');
  });

  it('muestra error con correo de formato inválido', () => {
    cy.visit('/frontend/pages/login.html');
    cy.get('#email').type('correo-invalido');
    cy.get('#password').type('algunpassword');
    cy.get('#btn-login').click();
    cy.get('#error-email').should('contain', 'válido');
  });

  it('muestra error con credenciales incorrectas', () => {
    cy.visit('/frontend/pages/login.html');
    cy.get('#email').type('noexiste@eduka.com');
    cy.get('#password').type('PasswordIncorrecta1@');
    cy.get('#btn-login').click();
    cy.get('#mensaje-error').should('be.visible');
  });

  it('el botón de mostrar/ocultar contraseña funciona', () => {
    cy.visit('/frontend/pages/login.html');
    cy.get('#password').type('miPassword123');
    cy.get('#password').should('have.attr', 'type', 'password');
    cy.get('#icon-pass').click();
    cy.get('#password').should('have.attr', 'type', 'text');
  });

  it('permite iniciar sesión con credenciales válidas y redirige al dashboard', () => {
    cy.visit('/frontend/pages/login.html');
    cy.get('#email').type('admin@eduka.com');
    cy.get('#password').type('password123');
    cy.get('#btn-login').click();

    cy.url().should('include', 'dashboard.html');
    cy.contains('Bienvenido').should('be.visible');
  });

  it('redirige al login si se intenta entrar al dashboard sin sesión', () => {
    cy.visit('/frontend/pages/dashboard.html');
    cy.url().should('include', 'login.html');
  });

  it('permite cerrar sesión correctamente', () => {
    cy.visit('/frontend/pages/login.html');
    cy.get('#email').type('admin@eduka.com');
    cy.get('#password').type('password123');
    cy.get('#btn-login').click();

    cy.url().should('include', 'dashboard.html');

    cy.on('window:confirm', () => true);
    cy.get('#btn-logout').click();

    cy.url().should('include', 'login.html');
  });

});