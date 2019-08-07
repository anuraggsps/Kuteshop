var config = {

	map: {
		'*': {
			'magicproduct'	: "Magiccart_Magicproduct/js/magicproduct",
		},
	},

	shim: {
		'magiccart/slick': {
			deps: ['jquery']
		},
		'magicproduct': {
			deps: ['jquery', 'magiccart/slick']
		},

	}
};
