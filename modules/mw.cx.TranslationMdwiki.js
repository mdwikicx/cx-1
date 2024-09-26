
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
	let url = 'https://ncc2c.toolforge.org/textp';

	// if (window.location.hostname === 'localhost') {
	// 	url = 'http://localhost:8000/textp';
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

async function from_simple(targetLanguage, title) {
	title = encodeURIComponent(title);
	const simple_url = "https://cxserver.wikimedia.org/v2/page/simple/" + targetLanguage + "/User:Mr.%20Ibrahem%2F" + title;

	const simple_result = await fetch(simple_url)
		.then((response) => {
			if (response.ok) {
				return response.json();
			}
		})

	if (simple_result) {
		simple_result.sourceLanguage = "en";
		// replace simple.wikipedia with en.wikipedia
		simple_result.segmentedContent = simple_result.segmentedContent.replace(/simple.wikipedia/g, "en.wikipedia");
		simple_result.segmentedContent = simple_result.segmentedContent.replace("User:Mr. Ibrahem/", "");
		simple_result.segmentedContent = simple_result.segmentedContent.replace("Drugbox", "Infobox drug");

	}
	return simple_result;
}

async function getMedwikiHtml(title, tr_type) {
	let end_point = "https://medwiki.toolforge.org";

	if (tr_type === "all") {
		// end_point = "https://mdwiki.org";
		end_point = "https://mdwiki.wmcloud.org";
	} else {
		title = "Md:" + title.replace(/\s/g, "_");
	}

	// Encode forward slashes
	// title = title.replace(/\//g, "%2F");
	title = encodeURIComponent(title);

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
	// إنشاء كائن DOMDocument وتحميل HTML فيه
	const parser = new DOMParser();
	const dom = parser.parseFromString(html, 'text/html');

	// الحصول على جميع العناصر من نوع <span>
	const elements = dom.getElementsByTagName('span');

	// تحويل العناصر إلى مصفوفة للتعامل معها في حلقة
	Array.from(elements).forEach(element => {
		// الحصول على HTML الخاص بالعنصر
		const nhtml = element.outerHTML;

		// التحقق مما إذا كان HTML يحتوي على 'unlinkedwikibase'
		if (nhtml.toLowerCase().includes('unlinkedwikibase')) {
			// إزالة العنصر من الوثيقة
			element.parentNode.removeChild(element);

			// استبدال HTML في النص الأصلي
			html = html.replace(nhtml, '');
		}
	});

	// إعادة النص المعدل
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

async function get_html_from_mdwiki(targetLanguage, title, fetchPageUrl, tr_type) {
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
	title = title.replace('/', '%2F');
	// ---
	var get_from_simple = false;
	// ---
	console.log("tr_type: ", tr_type)
	// ---
	if (get_from_simple) {
		var simple_result = from_simple(targetLanguage, title);
		if (simple_result) {
			return simple_result;
		};
	}

	var fetchPageUrl = "https://medwiki.toolforge.org/get_html/index.php";

	const new_way = true;

	// if (tr_type !== "all") {
	if (new_way || mw.user.getName() === "Mr. Ibrahem") {
		var resultx = await get_new(title, tr_type);
		if (resultx) {
			return resultx;
		}
	};
	// };

	const result = await get_html_from_mdwiki(targetLanguage, title, fetchPageUrl, tr_type);

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
