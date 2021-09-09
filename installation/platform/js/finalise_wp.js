/*
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

var steps        = ['updatehtaccess'];
var akeebaAjaxWP = null;
var totalSteps   = steps.length;

function runStep(curStep)
{
	var stepId = 'step' + curStep;
	var elStep = document.getElementById(stepId);

	akeeba.System.removeClass(elStep, 'akeeba-label--grey');
	akeeba.System.addClass(elStep, 'akeeba-label--teal');

	akeebaAjaxWP.callJSON({
		'view':   'finalise',
		'task':   'ajax',
		'method': steps[curStep - 1],
		'format': 'json'
	}, function () {
		akeeba.System.removeClass(elStep, 'akeeba-label--teal');
		akeeba.System.addClass(elStep, 'akeeba-label--green');

		if (curStep >= totalSteps)
		{
			document.getElementById('finalisationSteps').style.display = 'none';
			document.getElementById('finalisationInterface').style.display = 'block';
		}
		else
		{
			setTimeout(function () {
				runStep(curStep + 1)
			}, 100);
		}
	});
}

akeeba.System.documentReady(function () {
	akeebaAjaxWP = new akeebaAjaxConnector('index.php');
	setTimeout(function () {
		runStep(0)
	}, 100);
});
