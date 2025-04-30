document.addEventListener('DOMContentLoaded', () => {
    const buscarInput = document.getElementById('busqueda');
    const buscarBtn   = document.getElementById('buscar-btn');
    const resultados  = document.getElementById('resultados');
    const spinner     = document.getElementById('spinner');
    const formWrapper = document.getElementById('form-wrapper');

    // Búsqueda al presionar Enter
    buscarInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault(); // Evitar el comportamiento predeterminado del Enter
            buscarProductos(); // Llamar a la función de búsqueda
        }
    });

    // Búsqueda al hacer clic
    buscarBtn.addEventListener('click', buscarProductos);

    // Función principal de búsqueda
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
            resultados.innerHTML = `<p>❌ Error: ${err.message}</p>`;
        }
    }

    // Mostrar resultados en el DOM
    window.mostrarProductos = productos => {
        resultados.innerHTML = '';
        productos.forEach(product => {
            const nombre = product.product_name || 'Sin nombre';
            const img    = product.image_url    || 'img/no-image.png';

            const html = `
                <div class="producto">
                    <div class="producto-info">
                        <h3>${nombre}</h3>
                        <img src="${img}" width="100" onerror="this.src='img/no-image.png'">
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
    window.usarProducto = async (nombre, imagenUrl) => {
        // Enviar datos directamente al backend
        try {
            const blob = await (await fetch(imagenUrl)).blob();
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('imagen', blob, `${Date.now()}-${Math.random().toString(36).substring(7)}.jpg`);

            const response = await fetch('/public/agregar-producto.php', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            if (data.success) {
                alert('✅ Producto agregado correctamente a la base de datos.');
            } else {
                alert(`❌ Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Error al agregar el producto:', error);
            alert('✅ Producto agregado correctamente.');
        }
    };
});
