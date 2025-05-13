document.addEventListener('DOMContentLoaded', () => {
    const buscarInput = document.getElementById('busqueda');
    const buscarBtn   = document.getElementById('buscar-btn');
    const resultados  = document.getElementById('resultados');
    const spinner     = document.getElementById('spinner');
    const formWrapper = document.getElementById('form-wrapper');

    // Búsqueda al presionar Enter
    buscarInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            buscarProductos();
        }
    });

    buscarBtn.addEventListener('click', buscarProductos);

    async function buscarProductos() {
        const query = buscarInput.value.trim();
        if (!query) {
            resultados.innerHTML = '<p>⚠️ Ingresa un término de búsqueda.</p>';
            return;
        }

        resultados.innerHTML = '';
        spinner.style.display = 'block';

        try {
            const res  = await fetch(`/API IMG/php/proxy.php?query=${encodeURIComponent(query)}`);
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            const data = await res.json();
            spinner.style.display = 'none';

            if (!data.products || !data.products.length) {
                resultados.innerHTML = '<p>⚠️ No se encontraron productos.</p>';
                return;
            }

            // Filtrar por países
            const paises = [
                'spain','mexico','argentina','colombia','venezuela','chile','ecuador',
                'guatemala','cuba','bolivia','dominican republic','honduras','paraguay',
                'el salvador','nicaragua','costa rica','puerto rico','uruguay','panama',
                'equatorial guinea','united states','peru'
            ];

            const filtrados = data.products.filter(p => {
                const pais = (p.countries || '').toLowerCase();
                return paises.some(pa => pais.includes(pa));
            });

            mostrarProductos(filtrados.slice(0, 10));

        } catch (err) {
            spinner.style.display = 'none';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Ocurrió un error inesperado al buscar productos.'
            });
        }
    }

    window.mostrarProductos = productos => {
        resultados.innerHTML = '';
        productos.forEach(product => {
            const nombre = product.product_name || 'Sin nombre';
            const img    = product.image_url    || '/public/assets/img/denied.webp';

            const html = `
                <div class="producto">
                    <div class="producto-info">
                        <h3>${nombre}</h3>
                        <img src="${img}" width="100" onerror="this.src='/public/assets/img/denied.webp'">
                    </div>
                    <button type="button" onclick="usarProducto('${nombre.replace(/'/g, "\\'")}', '${img}')">
                        Usar este producto
                    </button>
                </div>
            `;
            resultados.insertAdjacentHTML('beforeend', html);
        });
    };

    // Rellena el formulario con nombre e imagen seleccionada
    function getSessionName() {
        const match = document.cookie.match(/session_name=([^;]+)/);
        return match ? match[1] : '';
    }

    window.usarProducto = async (nombre, imagenUrl) => {
        try {
            const blob = await (await fetch(imagenUrl)).blob();
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('imagen', blob, `${Date.now()}-${Math.random().toString(36).substring(7)}.jpg`);

            const sessionName = getSessionName();
            const response = await fetch('/public/agregar-producto.php?session_name=' + encodeURIComponent(sessionName), {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            // Verifica si la respuesta es JSON válida
            let data;
            try {
                data = await response.json();
            } catch (jsonError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Respuesta inesperada del servidor. Intenta iniciar sesión nuevamente.'
                });
                return;
            }

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Producto agregado correctamente a la base de datos.'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo agregar el producto.'
                }).then(() => {
                    if (data.message && data.message.includes('Sesión expirada')) {
                        window.location.href = '/login.php';
                    }
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Ocurrió un error inesperado al agregar el producto.'
            });
        }
    };
});
