const fetchGallerys = async (searchTerm) => {
	const res = await fetch(
		nggData.siteUrl +
			`/index.php?term=${searchTerm}&method=autocomplete&type=gallery&format=json&callback=json&limit=50`
	);

	return await res.json();
};

const fetchAlbums = async (searchTerm) => {
	const res = await fetch(
		nggData.siteUrl +
			`/index.php?term=${searchTerm}&method=autocomplete&type=album&format=json&callback=json&limit=50`
	);

	return await res.json();
};

const fetchImages = async (searchTerm) => {
	const res = await fetch(
		nggData.siteUrl +
			`/index.php?term=${searchTerm}&method=autocomplete&type=image&format=json&callback=json&limit=50`
	);

	return await res.json();
};

export { fetchGallerys, fetchAlbums, fetchImages };
