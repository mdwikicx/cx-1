
function add_sw_categories(html) {
	function one(cat) {
		console.log("add_sw_categories:", cat);
		return {
			"adapted": true,
			"sourceTitle": "Category:" + cat,
			"targetTitle": "Jamii:" + cat
		}
	}

	let categories = [];
	const regexInfoboxDrug = /infobox drug/i;
	const regexInfoboxMedicalCondition = /infobox medical condition/i;

	// if html has "infobox drug" categories.push( one("Madawa") );
	// if html has "infobox medical condition" categories.push( one("Magonjwa") );

	if (regexInfoboxDrug.test(html)) {
		categories.push(one("Madawa"));
	}

	if (regexInfoboxMedicalCondition.test(html)) {
		categories.push(one("Magonjwa"));
	}

	console.log(JSON.stringify(categories));
	console.log("add_sw_categories. Done");

	return categories;
}

async function postUrlParamsResult(endPoint, params = {}) {

	const options = {
		headers: { "Content-Type": "application/json" },
		method: 'POST',
		dataType: 'json',
		// mode: 'no-c',
		body: JSON.stringify(params)
	};

	const output = await fetch(endPoint, options)
		.then((response) => {
			if (!response.ok) {
				console.error(`Fetch Error: ${response.statusText}`);
				console.error(endPoint);
				return false;
			}
			return response.json();
		})

	return output;
}

async function doFixIt(text) {
	let url = 'https://ncc2c.toolforge.org/HtmltoSegments';

	// if (window.location.hostname === 'localhost') {
	// 	url = 'http://localhost:8000/HtmltoSegments';
	// }

	const data = { html: text };
	const responseData = await postUrlParamsResult(url, data);

	// Handle the response from your API
	if (!responseData) {
		return "";
	}

	if (responseData.error) {
		console.error('Error: ' + responseData.error);
		return "";
	}

	if (responseData.result) {
		return responseData.result
	}

	return "";
}

async function getMedwikiHtml(title, tr_type) {
	title = title.replace(/\s/g, "_");
	let end_point;
	({ end_point, title } = get_endpoint_and_title(tr_type, title));

	title = encodeURIComponent(title);
	title = title.replace('/', '%2F');

	const url = end_point + "/w/rest.php/v1/page/" + title + "/with_html";

	const options = {
		method: 'GET',
		headers: {
			'Content-Type': 'application/json',
			'User-Agent': 'WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)',
		},
		dataType: 'json'
	};
	let html;
	try {
		html = await fetch(url, options)
			.then((response) => {
				if (response.ok) {
					return response.json();
				}
			})
			.then((data) => {
				return data.html;
			})
			.catch((error) => {
				console.log(error);
			})
	} catch (error) {
		console.log(error);
	}
	return html;
}
function get_endpoint_and_title(tr_type, title) {
	let end_point = "https://medwiki.toolforge.org";

	if (tr_type === "all") {
		// if title contains slashes
		if (title.includes("/")) {
			title = title + "/fulltext";
		} else {
			// end_point = "https://mdwiki.wmcloud.org";
			end_point = "https://mdwiki.org";
		}
	}

	if (end_point == "https://medwiki.toolforge.org") {
		title = "Md:" + title;
	}
	return { end_point, title };
}

function getRevision_old(HTMLText) {
	if (HTMLText !== '') {
		const matches = HTMLText.match(/Redirect\/revision\/(\d+)/);
		if (matches && matches[1]) {
			const revision = matches[1];
			return revision;
		}
	}
	return "";
}
function removeUnlinkedWikibase(html) {
	const parser = new DOMParser();
	const dom = parser.parseFromString(html, 'text/html');

	const elements = dom.getElementsByTagName('span');

	Array.from(elements).forEach(element => {
		const nhtml = element.outerHTML;

		if (nhtml.toLowerCase().includes('unlinkedwikibase') || nhtml.toLowerCase().includes('mdwiki revid')) {
			element.parentNode.removeChild(element);

			html = html.replace(nhtml, '');
		}
	});

	return html;
}

async function get_new(title, tr_type) {

	const out = {
		sourceLanguage: "mdwiki",
		title: title,
		revision: "",
		segmentedContent: "",
		categories: []
	}
	var html = await getMedwikiHtml(title, tr_type);

	if (!html) {
		console.log("getMedwikiHtml: not found");
		return false;
	};

	html = removeUnlinkedWikibase(html);

	out.revision = getRevision_old(html);

	out.segmentedContent = await doFixIt(html);
	if (out.segmentedContent == "") {
		console.log("doFixIt: not found");
		return false;
	};
	return out;
}

async function get_new_2025(title, tr_type) {

	const fetchParams = {
		title: title
	};
	if (tr_type === "all") {
		fetchParams.all = "all";
	}
	var fetchPageUrl = "https://medwiki.toolforge.org/new_html/index.php?" + $.param(fetchParams);

	const options = {
		method: 'GET',
		dataType: 'json'
	};
	const result = await fetch(fetchPageUrl, options)
		.then((response) => {
			if (!response.ok) {
				console.error("Error fetching source page: " + response.statusText);
				return Promise.reject(response);
			}
			return response.json();

		})
		.catch((error) => {
			console.error("Network error: ", error);
		});
	return result;
}

async function get_new_2024(title) {
	var title = title.replace(/['" :/]/g, "_");

	const out = {
		sourceLanguage: "mdwiki",
		title: title,
		revision: "",
		segmentedContent: "",
		categories: []
	}

	const url = "https://medwiki.toolforge.org/mdtexts/segments.php?title=" + title;

	const options = {
		method: 'GET',
		headers: {
			'Content-Type': 'application/json',
			'User-Agent': 'WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)',
		},
		dataType: 'json'
	};
	let html;
	try {
		html = await fetch(url, options)
			.then((response) => {
				if (response.ok) {
					return response.json();
				}
			})
			.then((data) => {
				return data.html;
			})
			.catch((error) => {
				console.log(error);
			})
	} catch (error) {
		console.log(error);
	}
	if (!html) {
		console.log("getMedwikiHtml: not found");
		return false;
	};

	out.revision = "5200";

	var html2 = html.replaceAll("&#34;", '"');
	const matches = html2.match(/Mdwiki_revid"\},"params":\{"1":\{"wt":"(\d+)"\}\}/);
	if (matches && matches[1]) {
		out.revision = matches[1];
		console.log("get_new_2024 ", out.revision);
	}
	html = removeUnlinkedWikibase(html);

	out.segmentedContent = html;

	return out;
}

async function get_html_from_mdwiki(targetLanguage, title, tr_type) {
	var fetchPageUrl = "https://medwiki.toolforge.org/get_html/index.php";

	const fetchParams = {
		sourcelanguage: "mdwiki",
		targetlanguage: targetLanguage,
		tr_type: tr_type,
		title: title
	};
	if (tr_type === "all") {
		fetchParams.all = "all";
	} else {
		fetchParams.section0 = 1;
	}
	fetchPageUrl = fetchPageUrl + "?" + $.param(fetchParams);

	const options = {
		method: 'GET',
		dataType: 'json'
	};
	const result = await fetch(fetchPageUrl, options)
		.then((response) => {
			if (!response.ok) {
				console.error("Error fetching source page: " + response.statusText);
				return Promise.reject(response);
			}
			return response.json();

		})
		.catch((error) => {
			console.error("Network error: ", error);
		});

	return result;
};

async function fetchSourcePageContent_mdwiki_new(wikiPage, targetLanguage, siteMapper, tr_type) {
	// Manual normalisation to avoid redirects on spaces but not to break namespaces
	var title = wikiPage.getTitle().replace(/ /g, '_');
	// ---
	console.log("tr_type: ", tr_type)
	// ---
	var use2025 = false;

	if (use2025) {
		if (mw.user.getName() === "Mr. Ibrahem" || mw.user.getName() === "Mr. Ibrahem 1") {
			// var resultx = await get_new_2024(title);
			var resultx = await get_new_2025(title, tr_type);
			if (resultx) {
				return resultx;
			}
		};
	};

	var resultn = await get_new(title, tr_type);

	if (resultn) {
		return resultn;
	}

	const result = await get_html_from_mdwiki(targetLanguage, title, tr_type);

	return result;

};

async function fetchSourcePageContent_mdwiki(wikiPage, targetLanguage, siteMapper, tr_type) {

	let result = await fetchSourcePageContent_mdwiki_new(wikiPage, targetLanguage, siteMapper, tr_type);

	if (result && result.segmentedContent && targetLanguage == "sw") {
		let categories = add_sw_categories(result.segmentedContent);
		result.categories = categories;
	}
	return result;

};

mw.cx.TranslationMdwiki = {
	fetchSourcePageContent_mdwiki
}
