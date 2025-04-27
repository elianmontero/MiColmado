async function buscarProductos() {
    const query = document.getElementById('busqueda').value;
    const resultadosDiv = document.getElementById('resultados');
    resultadosDiv.innerHTML = "<p>🔎 Buscando productos...</p>";

    try {
        const apiUrl = `php/proxy.php?query=${encodeURIComponent(query)}`;
        const response = await fetch(apiUrl);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        resultadosDiv.innerHTML = "";

        if (!data.products || data.products.length === 0) {
            resultadosDiv.innerHTML = "<p>⚠️ No se encontraron productos para tu búsqueda. Intenta con otro término.</p>";
            return;
        }

        const paisesHispanohablantes = [
            "spain", "mexico", "argentina", "colombia", "venezuela", "chile", "ecuador",
            "guatemala", "cuba", "bolivia", "dominican republic", "honduras", "paraguay",
            "el salvador", "nicaragua", "costa rica", "puerto rico", "uruguay", "panama",
            "equatorial guinea", "united states"
        ];

        let currentIndex = 0; // Índice inicial de los productos mostrados
        const pageSize = 25; // Tamaño de cada página
        let verMasBtn = null; // Inicializar la variable como null

        // Función para mostrar productos en bloques de 25
        const mostrarMasProductos = () => {
            const nextIndex = currentIndex + pageSize;
            const productosParaMostrar = data.products
                .filter(product => {
                    const pais = product.countries || "";
                    return paisesHispanohablantes.some(paisHispano =>
                        pais.toLowerCase().includes(paisHispano)
                    );
                })
                .slice(currentIndex, nextIndex);

            mostrarProductos(productosParaMostrar, resultadosDiv);
            currentIndex = nextIndex;

            // Si no hay más productos para mostrar, ocultar el botón
            if (verMasBtn) {
                if (currentIndex >= data.products.length) {
                    verMasBtn.style.display = "none";
                } else {
                    verMasBtn.style.display = "block";
                }
            }
        };

        // Mostrar los primeros 25 productos
        mostrarMasProductos();

        // Crear el botón "Ver más"
        verMasBtn = document.createElement("button");
        verMasBtn.textContent = "Ver más";
        verMasBtn.className = "ver-mas-btn";
        verMasBtn.onclick = () => {
            verMasBtn.disabled = true;
            verMasBtn.textContent = "Cargando...";

            setTimeout(() => {
                mostrarMasProductos();
                verMasBtn.disabled = false;
                verMasBtn.textContent = "Ver más";
            }, 1000);
        };
        resultadosDiv.appendChild(verMasBtn);
    } catch (error) {
        resultadosDiv.innerHTML = `<p>❌ Error al buscar productos: ${error.message}</p>`;
    }
}

function mostrarProductos(productos, contenedor) {
    productos.forEach(product => {
        const nombre = product.product_name || "Sin nombre";
        const marca = product.brands || "N/A";
        const pais = product.countries || "Desconocido";
        const imagen = product.image_url || null;

        // Si no hay imagen, no mostrar el producto
        if (!imagen) return;

        const productoHTML = `
            <div class="producto">
                <h3>${nombre}</h3>
                <p class="marca">${marca}</p>
                <img src="${imagen}" width="100" onerror="this.src='img/no-image.png'">
                <button onclick="guardarProductoPersonalizado(this, '${product.code}')">Guardar</button>
            </div>
        `;
        contenedor.innerHTML += productoHTML;
    });
}

async function guardarProductoPersonalizado(boton, code) {
    const contenedor = boton.parentElement;
    const nombre = contenedor.querySelector('.nombre-input').value;
    const precio = contenedor.querySelector('.precio-input').value;
    const imagen_url = contenedor.querySelector('.imagen-input').value;

    try {
        const response = await fetch(`php/guardar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: code,
                nombre: nombre || 'Sin nombre',
                marca: contenedor.querySelector('.marca').textContent.replace('Marca: ', ''),
                categorias: '',
                imagen_url: imagen_url,
                pais: contenedor.querySelector('p:nth-of-type(2)').textContent.replace('País: ', ''),
                precio: precio || 'N/A'
            })
        });

        alert(response.ok ? "✅ Producto guardado con tu versión personalizada!" : "⚠️ Error al guardar el producto.");
    } catch (error) {
        alert("Error: " + error.message);
    }
}
