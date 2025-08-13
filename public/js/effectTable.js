
Swal.fire('Hello !', 'Ça marche 🎉', 'success');
function updateStatus(id) {
        try {
        
            let response = fetch(`/update/status/{${id}}`, {
                method: 'POST',
                headers: { "X-Requested-with": "XMLHttpRequest" },
                //body: JSON.stringify({"id":id}),
            });
            
            //document.getElementById('resulut').innerHTML = response.text();
            
        } catch (error) {
        
            document.getElementById('resulut').innerHTML = "Erreur lors de la récupération des données:" + error;
        }
}
