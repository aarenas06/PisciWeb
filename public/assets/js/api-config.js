/* Global API config
   - Define `API_BASE` (ajusta según tu estructura)
   - Usa `apiUrl(path)` para construir rutas cortas:
     fetch(apiUrl('proyectos/.../controller.php'), { method:'POST', body })
*/
(function (window) {
    // Ajusta si tus endpoints viven en otra carpeta; por defecto raíz relativa
    window.API_BASE = window.API_BASE || '';

    window.apiUrl = function (path) {
        if (typeof path !== 'string') return window.API_BASE;
        var base = (window.API_BASE || '').replace(/\/+$|^\s+|\s+$/g, '');
        var p = path.replace(/^\/+/, '');
        if (base === '') return '/' + p;
        return base.replace(/\/+$/, '') + '/' + p;
    };
})(window);
