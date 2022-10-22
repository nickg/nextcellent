const fetchGallerys = async (searchTerm) => {
    const res =  await fetch(nggData.siteUrl + `/index.php?term=${searchTerm}&method=autocomplete&type=gallery&format=json&callback=json&limit=50`)

    return await res.json();
}

export {
    fetchGallerys
}
