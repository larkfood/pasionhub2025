/*
 * SocialMedia
 *
 * Social Media Supported:
 * facebook, twitter, x-twitter, linkedin, whatsapp, telegram, messenger, pinterest, tumblr, vk,
 * mastodon, odnoklassniki, pocket, reddit, microsoft-teams, viber, email
 *
 * Resources:
 * https://github.com/kytta/shareon/
 * https://github.com/AyumuKasuga/SocialShare
 *
 * Usage:
 * SocialShare.init();
 *
 * <div class="social-share">
 * <a class="facebook"><i class="fa-brands fa-square-facebook"></i><a>
 * <a class="x-twitter"><i class="fa-brands fa-square-x-twitter"></i><a>
 * ...
 * </div>
 *
 */

const SocialShare = (function (module) {
	"use strict";
	
	/**
	 * Map of social networks to their respective URL builders.
	 *
	 * The `d` argument of each builder is the object with the page metadata, such as page title, URL, author name, etc.
	 *
	 * @type {{ [network: string]: (d: {
	 *   url: string,
	 *   title?: string,
	 *   media?: string,
	 *   text?: string,
	 *   via?: string,
	 *   fbAppId?: string,
	 * }) => string}}
	 */
	const urlBuilderMap = {
		facebook: (d) => {
			let url = `https://www.facebook.com/sharer.php?s=100&p[title]=${d.title}&u=${d.url}&t=${d.title}&p[url]=${d.url}`;
			// let url = `https://www.facebook.com/sharer/sharer.php?u=${d.url}&t=${d.title}`;
			url += d.text ? `&p[summary]=${d.text}` : '';
			url += d.hashtags ? `&hashtag=%23${d.hashtags.split('%2C')[0]}` : '';
			
			return url;
		},
		twitter: (d) => {
			let url = `https://twitter.com/intent/tweet?url=${d.url}&text=${d.title}`;
			url += d.via ? `&via=${d.via}` : '';
			url += d.hashtags ? `&hashtags=${d.hashtags}` : '';
			
			return url;
		},
		'x-twitter': (d) => {
			let url = `https://twitter.com/intent/tweet?url=${d.url}&text=${d.title}`;
			url += d.via ? `&via=${d.via}` : '';
			url += d.hashtags ? `&hashtags=${d.hashtags}` : '';
			
			return url;
		},
		linkedin: (d) => {
			let url = `https://www.linkedin.com/shareArticle?mini=true&url=${d.url}&title=${d.title}&source=${d.url}`;
			// let url = `https://www.linkedin.com/sharing/share-offsite/?url=${d.url}&title=${d.title}`;
			url += d.text ? `&summary=${d.text}` : '';
			
			return url;
		},
		whatsapp: (d) => {
			// whatsapp://send?text={title} {url}
			let url = `https://wa.me/?text=${d.title}%0D%0A${d.url}`;
			url += d.text ? `%0D%0A%0D%0A${d.text}` : '';
			
			return url;
		},
		telegram: (d) => {
			return `https://telegram.me/share/url?text=${d.title}&url=${d.url}`;
		},
		messenger: (d) => {
			return `https://www.facebook.com/dialog/send?app_id=${d.fbAppId}&link=${d.url}&redirect_uri=${d.url}`;
		},
		pinterest: (d) => {
			let url = `https://www.pinterest.com/pin/create/button/?url=${d.url}&description=${d.title}`;
			url += d.media ? `&media=${d.media}` : '';
			
			return url;
		},
		tumblr: (d) => {
			// let url = `https://tumblr.com/share?s=&v=3&t=${d.title}&u=${d.url}`;
			let url = `https://www.tumblr.com/widgets/share/tool?posttype=link&title=${d.title}&content=${d.url}&canonicalUrl=${d.url}`;
			url += d.hashtags ? `&tags=${d.hashtags}` : '';
			url += d.text ? `&caption=${d.text}` : '';
			url += d.via ? `&show-via=${d.via}` : '';
			
			return url;
		},
		vk: (d) => {
			// let url = `https://vkontakte.ru/share.php?url=${d.url}&title=${d.title}&noparse=true`;
			let url = `https://vk.com/share.php?url=${d.url}&title=${d.title}`;
			url += d.text ? `&description=${d.text}` : '';
			url += d.media ? `&image=${d.media}` : '';
			
			return url;
		},
		mastodon: (d) => {
			let url = `https://toot.kytta.dev/?text=${d.title}%0D%0A${d.url}`;
			url += d.text ? `%0D%0A%0D%0A${d.text}` : '';
			url += d.via ? `%0D%0A%0D%0A${d.via}` : '';
			
			return url;
		},
		odnoklassniki: (d) => {
			let url = `https://connect.ok.ru/offer?url=${d.url}&title=${d.title}`;
			url += d.media ? `&imageUrl=${d.media}` : '';
			
			return url;
		},
		pocket: (d) => {
			return `https://getpocket.com/edit.php?url=${d.url}`;
		},
		reddit: (d) => {
			return `https://www.reddit.com/submit?title=${d.title}&url=${d.url}`;
		},
		teams: (d) => {
			let url = `https://teams.microsoft.com/share?href=${d.url}`;
			url += d.text ? `&msgText=${d.text}` : '';
			
			return url;
		},
		viber: (d) => {
			let url = `viber://forward?text=${d.title}%0D%0A${d.url}`;
			url += d.text ? `%0D%0A%0D%0A${d.text}` : '';
			
			return url;
		},
		email: (d) => {
			return `mailto:?subject=${d.title}&body=${d.url}`;
		},
	};
	
	const openUrl = (buttonUrl, options = {}) => () => {
		let parameters = 'noopener,noreferrer';
		
		let screenWidth = screen.width;
		let screenHeight = screen.height;
		let popupWidth = options.width ? options.width : (screenWidth - (screenWidth * 0.2));
		let popupHeight = options.height ? options.height : (screenHeight - (screenHeight * 0.2));
		let left = (screenWidth/2)-(popupWidth/2);
		let top = (screenHeight/2)-(popupHeight/2);
		
		parameters += ',toolbar=0,status=0,width=' + popupWidth + ',height=' + popupHeight + ',top=' + top + ',left=' + left;
		
		window.open(buttonUrl, '_blank', parameters);
		
		return false;
	};
	
	const init = (defaults = {}) => {
		const socialShareContainers = document.querySelectorAll(".social-share");
		
		// Iterate over <div class="social-share">
		for (const container of socialShareContainers) {
			// Iterate over children of <div class="social-share">
			for (const child of container.children) {
				if (child) {
					const classListLength = child.classList.length;
					
					// Get all values
					const dataObj = {
						url: child.dataset.url || container.dataset.url || defaults.url || window.location.href,
						title: child.dataset.title || container.dataset.title || defaults.title || document.title,
						text: child.dataset.text || container.dataset.text || defaults.text || '',
						media: child.dataset.media || container.dataset.media || defaults.media || '',
						hashtags: child.dataset.hashtags || container.dataset.hashtags || defaults.hashtags || '',
						via: child.dataset.via || container.dataset.via || defaults.via || '',
						fbAppId: child.dataset.fbAppId || container.dataset.fbAppId || defaults.fbAppId || ''
					};
					let popUpOptions = {};
					if (defaults.width && defaults.height) {
						popUpOptions = {
							width: defaults.width,
							height: defaults.height,
						};
					}
					
					// Iterate over classes of the child element
					for (let k = 0; k < classListLength; k += 1) {
						const currentClass = child.classList.item(k);
						
						// If it's "Copy URL"
						if (currentClass === 'copy-url') {
							child.addEventListener('click', () => {
								navigator.clipboard.writeText(dataObj.url);
								child.classList.add('done');
								setTimeout(() => {
									child.classList.remove('done');
								}, 1000);
							});
						}
						
						// If it's "Print"
						if (currentClass === "print") {
							child.addEventListener('click', () => {
								window.print();
							});
						}
						
						// If it's "Web Share"
						if (currentClass === 'web-share') {
							const webShareData = {
								title: dataObj.title,
								text: dataObj.text,
								url: dataObj.url,
							};
							
							if (navigator.canShare && navigator.canShare(webShareData)) {
								child.addEventListener('click', () => {
									navigator.share(webShareData);
								});
							} else {
								child.style.display = 'none';
							}
						}
						
						// If it's one of the networks
						if (Object.prototype.hasOwnProperty.call(urlBuilderMap, currentClass)) {
							const url = urlBuilderMap[currentClass](dataObj);
							
							if (child.tagName.toLowerCase() === 'a') {
								child.setAttribute('href', url);
								child.setAttribute('rel', 'noopener noreferrer');
								child.setAttribute('target', '_blank');
							}
							
							child.addEventListener('click', openUrl(url, popUpOptions));
							
							break; // Once a network is detected we don't want to check further
						}
					}
				}
			}
		}
	};
	
	
	// Define an object that will serve as a module (If 'module' is not defined or is null)
	if (!module) {
		module = {};
	}
	module.init = init;
	
	// Return the public module with a method for initialization
	return module;
})();
