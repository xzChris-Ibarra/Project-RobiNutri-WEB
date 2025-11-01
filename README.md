# Proyecto Final 4to. Cuatrimestre: RobiNutri - Web

## 1. Descripción del Proyecto

🔶 Con el propósito de desarrollar un chatbot inteligente y didáctico potenciado por IA, llamado RobiNutri, enfocado en promover hábitos alimenticios saludables en niños de entre 8 y 12 años en la ciudad de Mexicali, Baja California.

🔷 El asistente virtual estará representado por una mascota amigable llamada RobiNutri, quien guiará a los niños a través de un lenguaje empático, lúdico y educativo sobre nutrición infantil.

🔺 México enfrenta un problema creciente de obesidad infantil, especialmente en la primera infancia. RobiNutri surge como una herramienta innovadora que utiliza inteligencia artificial y diseño centrado en el niño para motivar, enseñar y acompañar a los niños en su formación de buenos hábitos alimenticios. El proyecto busca ser no solo una solución académica, sino también un producto con potencial de impacto social, escalabilidad y valor comercial.

---

## 2. Metodología de Desarrollo

**Metodología Seleccionada:** Metodología Tradicional - Cascada

**Justificación:**
Se eligió el modelo tradicional porque el proyecto No es nuevo, y por ende, los requisitos ya están establecidos. Está metodología nos permite trabajar y entregar el proyecto de manera estructurada.

---

## 3. Requerimientos del Sistema

### 3.1 Requerimientos Funcionales (RF)

**RF-001:** El sistema permitirá que los usuarios interactuén con el chat sin un registro previo, etiquetándolos como *invitados*.
**RF-002:** El sistema debe permitir a un nuevo usuario registrarse con: nombre, apellido, fecha de nacimiento (+18 obligatorio o ayuda parental), género, correo electrónico y contraseña.
**RF-003:** El sistema debe permitir a un usuario existente iniciar sesión (autenticación) con correo y contraseña.
**RF-004:** El usuario debe poder registrar mínimo a 1 hijo/a, con sus datos: nombre, apellido, edad, alergías, enfermedades, observaciones. En esta misma interfaz, poder crear, editar o eliminar perfiles a elección del usuario.
**RF-005:** Una vez la sesión iniciada, el sistema debe llevar al usuario a la interfaz principal con el chat, y mostrar un saludo personalizado.
**RF-006:** El sistema debe tener las siguientes opciones en el menú principal: barra desplegable para acceder a menú ajustes, historial de chats, nuevo chat, ícono desplegable con perfiles y sus ajustes, barra desplegable sección nutriologo (representativa por el momento), barra de chat principal.
**RF-007:** El chatbot responderá satisfactoriamente las dudas del usuario sobre temas alimenticios.
**RF-008:** El chatbot no responderá preguntas que se encuentren fuera del tópico establecido, en caso de, responderá con un mensaje amigable negándose a responder.

### 3.2 Requerimientos No Funcionales (RNF)

**RNF-001 (Rendimiento):** El tiempo de carga de la página principal no debe exceder los 5 segundos.
**RNF-002 (Usabilidad):** El sitio web debe ser *responsive*, adaptándose correctamente a pantallas de escritorio y dispositivos móviles (teléfonos).
**RNF-003 (Seguridad):** Las contraseñas de los usuarios deben almacenarse en la base de datos de forma encriptada (hasheada).
**RNF-004 (Tecnología):** El *frontend* se desarrollará con HTML, CSS y JavaScript.
**RNF-005 (Tecnología):** El *backend* se desarrollará con PHP y la base de datos será MySQL, IA con API de OpenAI (ChatGPT) y el despliegue con Vercel. 
**RNF-006 (Compatibilidad):** El sitio debe funcionar correctamente en las últimas versiones de los navegadores más comunes y utilizados (Google Chrome, Firefox, Safari, Microsoft Edge, Brave).

## 4. Autores

*Christian Ibarra - Desarrollador Frontend y Diseñador UI/UX.*
*Gerardo Dávalos - Desarrollador Full-Stack.*
*Diego Ruíz - Diseñador UI/UX y Desarrollador Frontend.*
