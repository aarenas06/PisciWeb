/**
 * Session Keep-Alive Manager
 * Mantiene activa la sesión PHP haciendo requests periódicos
 * Previene timeout de sesión durante períodos de inactividad
 */

(function() {
    // ============================================================
    // CONFIGURACIÓN
    // ============================================================
    const SESSION_KEEP_ALIVE_CONFIG = {
        enabled: true,
        intervalMs: 3 * 60 * 1000,      // 3 minutos (más frecuente que antes)
        maxRetries: 3,
        retryDelayMs: 2000,             // 2 segundos entre reintentos
        warningThresholdMs: 20 * 60000, // Advertencia a los 20 minutos sin actividad
        timeoutMs: 5000,                // Timeout para cada request
        logEnabled: false,              // Cambiar a true para debug
        endpoint: '/PisciWeb/public/keep-alive.php'
    };

    // ============================================================
    // VARIABLES DE ESTADO
    // ============================================================
    let keepAliveTimer = null;
    let lastActivityTime = Date.now();
    let requestInProgress = false;
    let failureCount = 0;

    // ============================================================
    // FUNCIONES AUXILIARES
    // ============================================================
    
    /**
     * Log con timestamp
     */
    function log(message, data = null) {
        if (!SESSION_KEEP_ALIVE_CONFIG.logEnabled) return;
        const timestamp = new Date().toLocaleTimeString();
        console.log(`[${timestamp}] Keep-Alive: ${message}`, data || '');
    }

    /**
     * Actualiza el tiempo de última actividad
     */
    function updateActivityTime() {
        lastActivityTime = Date.now();
        log('Actividad detectada');
    }

    /**
     * Calcula el tiempo de inactividad en minutos
     */
    function getInactivityMinutes() {
        return Math.round((Date.now() - lastActivityTime) / 1000 / 60);
    }

    // ============================================================
    // KEEP-ALIVE LOGIC
    // ============================================================

    /**
     * Envía un ping al servidor para mantener la sesión activa
     */
    async function sendKeepAlivePing() {
        if (requestInProgress) {
            log('Request ya en progreso, saltando...');
            return false;
        }

        requestInProgress = true;
        const inactivityMinutes = getInactivityMinutes();
        
        log(`Enviando ping (inactividad: ${inactivityMinutes}m)...`);

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), SESSION_KEEP_ALIVE_CONFIG.timeoutMs);

            // Usar POST en lugar de GET para mayor compatibilidad
            const response = await fetch(SESSION_KEEP_ALIVE_CONFIG.endpoint, {
                method: 'POST',
                credentials: 'include',
                cache: 'no-store',
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=keep_alive&timestamp=' + Date.now()
            });

            clearTimeout(timeoutId);

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    failureCount = 0;
                    log('✓ Ping exitoso - Sesión renovada');
                    return true;
                } else {
                    log('⚠ Respuesta sin éxito: ' + (data.error || 'Unknown'));
                    return false;
                }
            } else if (response.status === 401) {
                log('❌ Sesión expirada (401)');
                showSessionWarning('Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.');
                return false;
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

        } catch (error) {
            failureCount++;
            const errorMsg = error.name === 'AbortError' ? 'Timeout' : error.message;
            
            log(`✗ Error en ping (${failureCount}/${SESSION_KEEP_ALIVE_CONFIG.maxRetries}): ${errorMsg}`);

            if (failureCount >= SESSION_KEEP_ALIVE_CONFIG.maxRetries) {
                log('❌ Máximo número de reintentos alcanzado');
                showSessionWarning('No se pudo conectar con el servidor. Verifica tu conexión a internet.');
                return false;
            }

            return false;
        } finally {
            requestInProgress = false;
        }
    }

    /**
     * Muestra una notificación pequeña tipo toast en la esquina
     */
    function showSessionWarning(message) {
        // Crear contenedor si no existe
        let container = document.getElementById('session-notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'session-notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 99999;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            `;
            document.body.appendChild(container);
        }

        // Crear notificación
        const notification = document.createElement('div');
        const notificationId = 'notification-' + Date.now();
        notification.id = notificationId;
        notification.style.cssText = `
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #fff;
            padding: 16px 20px;
            margin-bottom: 10px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
            font-size: 14px;
            line-height: 1.4;
        `;

        // Icono de advertencia
        const icon = document.createElement('span');
        icon.innerHTML = '⚠️';
        icon.style.fontSize = '20px';
        icon.style.flexShrink = '0';

        // Contenido de texto
        const text = document.createElement('span');
        text.textContent = message;
        text.style.flex = '1';

        // Botón de cerrar
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '✕';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            opacity: 0.8;
        `;
        closeBtn.onmouseover = () => closeBtn.style.opacity = '1';
        closeBtn.onmouseout = () => closeBtn.style.opacity = '0.8';
        closeBtn.onclick = () => removeNotification(notificationId);

        notification.appendChild(icon);
        notification.appendChild(text);
        notification.appendChild(closeBtn);
        container.appendChild(notification);

        // Auto-remover después de 8 segundos (o 15 si es crítico)
        const duration = message.includes('expirada') ? 15000 : 8000;
        setTimeout(() => removeNotification(notificationId), duration);

        // Agregar estilos de animación si no existen
        addAnimationStyles();
    }

    /**
     * Remueve una notificación
     */
    function removeNotification(notificationId) {
        const notification = document.getElementById(notificationId);
        if (notification) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    /**
     * Agrega estilos CSS para animaciones si no existen
     */
    function addAnimationStyles() {
        if (document.getElementById('session-notification-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'session-notification-styles';
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
            
            @media (max-width: 640px) {
                #session-notification-container {
                    left: 10px !important;
                    right: 10px !important;
                    top: 10px !important;
                }
                
                #session-notification-container > div {
                    min-width: auto !important;
                    max-width: 100% !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Inicia el ciclo de keep-alive
     */
    function startKeepAlive() {
        if (!SESSION_KEEP_ALIVE_CONFIG.enabled) {
            log('Keep-Alive deshabilitado');
            return;
        }

        log('Iniciando Keep-Alive cada ' + (SESSION_KEEP_ALIVE_CONFIG.intervalMs / 1000 / 60) + ' minutos');

        // Enviar ping inicial
        sendKeepAlivePing();

        // Establecer intervalo
        keepAliveTimer = setInterval(() => {
            const inactivityMinutes = getInactivityMinutes();

            // Mostrar advertencia si ha pasado mucho tiempo
            if (inactivityMinutes > 30 && inactivityMinutes % 10 === 0) {
                log(`⚠ Inactividad prolongada: ${inactivityMinutes} minutos`);
            }

            sendKeepAlivePing();
        }, SESSION_KEEP_ALIVE_CONFIG.intervalMs);
    }

    /**
     * Detiene el ciclo de keep-alive
     */
    function stopKeepAlive() {
        if (keepAliveTimer) {
            clearInterval(keepAliveTimer);
            keepAliveTimer = null;
            log('Keep-Alive detenido');
        }
    }

    // ============================================================
    // EVENT LISTENERS PARA DETECTAR ACTIVIDAD
    // ============================================================

    /**
     * Detecta actividad del usuario
     */
    function setupActivityDetection() {
        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];

        activityEvents.forEach(event => {
            document.addEventListener(event, updateActivityTime, true);
        });

        log('Detectores de actividad inicializados');
    }

    /**
     * Limpiar event listeners
     */
    function cleanupActivityDetection() {
        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];

        activityEvents.forEach(event => {
            document.removeEventListener(event, updateActivityTime, true);
        });
    }

    // ============================================================
    // MANEJO DE VISIBILIDAD DE PÁGINA
    // ============================================================

    /**
     * Pause/Resume keep-alive cuando la pestaña se muestra/oculta
     */
    function setupVisibilityDetection() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                log('Página oculta - pausando keep-alive');
                stopKeepAlive();
            } else {
                log('Página visible - reanudando keep-alive');
                startKeepAlive();
            }
        });
    }

    // ============================================================
    // INICIALIZACIÓN Y EXPOSICIÓN GLOBAL
    // ============================================================

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    function initialize() {
        log('Inicializando Keep-Alive Manager...');
        setupActivityDetection();
        setupVisibilityDetection();
        startKeepAlive();
        log('✓ Keep-Alive Manager listo');
    }

    // Limpiar al cerrar la página
    window.addEventListener('beforeunload', () => {
        stopKeepAlive();
        cleanupActivityDetection();
    });

    // Exponer API global para control manual
    window.SessionKeepAlive = {
        start: startKeepAlive,
        stop: stopKeepAlive,
        ping: sendKeepAlivePing,
        config: SESSION_KEEP_ALIVE_CONFIG,
        getInactivityMinutes: getInactivityMinutes,
        log: (msg) => {
            SESSION_KEEP_ALIVE_CONFIG.logEnabled = true;
            log(msg);
        },
        enableLogging: () => {
            SESSION_KEEP_ALIVE_CONFIG.logEnabled = true;
            log('Logging habilitado');
        },
        disableLogging: () => {
            SESSION_KEEP_ALIVE_CONFIG.logEnabled = false;
        }
    };

    log('Keep-Alive Manager cargado globalmente en window.SessionKeepAlive');

})();
