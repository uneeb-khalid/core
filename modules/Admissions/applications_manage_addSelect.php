<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Http\Url;
use Gibbon\Forms\Form;
use Gibbon\Domain\Forms\FormGateway;
use Gibbon\Domain\Admissions\AdmissionsAccountGateway;
use Gibbon\Services\Format;


if (isActionAccessible($guid, $connection2, '/modules/Admissions/applications_manage_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID');
    $search = $_REQUEST['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Applications'), 'applications_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID, 'search' => $search])
        ->add(__('Add Application'));

    // Display form actions
    if (!empty($search)) {
        $page->navigator->addSearchResultsAction(Url::fromModuleRoute('Admissions', 'applications_manage')->withQueryParams($urlParams));
    }
    
    $form = Form::create('addApplication', $session->get('absoluteURL').'/modules/Admissions/applications_manage_addSelectProcess.php');
    $form->setDescription(__('You can use this page to manually create an application form on behalf of another user. If the user already exists in the system, be sure to select them below so that their application will be connected to their admissions account. If the user does not exist, a new admissions account will be created.'));
    $form->addHiddenValue('address', $session->get('address'));

    // QUERY
    $formGateway = $container->get(FormGateway::class);
    $criteria = $formGateway->newQueryCriteria(true)
        ->sortBy('name', 'ASC')
        ->filterBy('type', 'Application')
        ->filterBy('active', 'Y');

    $forms = $formGateway->queryForms($criteria);

    $row = $form->addRow();
        $row->addLabel('gibbonFormID', __('Application Form'));
        $row->addSelect('gibbonFormID')->fromDataSet($forms, 'gibbonFormID', 'name')->required()->placeholder();

    $types = array(
        'blank'   => __('Blank Application'),
        'account' => __('Current').' '.__('Admissions Account'),
        // 'family'  => __('Current').' '.__('Family'),
        'person'  => __('Current').' '.__('User'),
    );

    $row = $form->addRow();
        $row->addLabel('applicationType', __('Type'));
        $row->addSelect('applicationType')->fromArray($types)->required()->placeholder();

    // QUERY
    $admissionsAccountGateway = $container->get(AdmissionsAccountGateway::class);
    $criteria = $admissionsAccountGateway->newQueryCriteria(true)
        ->searchBy($admissionsAccountGateway->getSearchableColumns(), $search)
        ->sortBy(['surname', 'preferredName']);

    $accounts = $admissionsAccountGateway->queryAdmissionsAccounts($criteria)->toArray();
    $accounts = array_reduce($accounts, function ($group, $item) {
        $name = !empty($item['surname']) ? Format::name('', $item['preferredName'], $item['surname'], 'Parent', true) : __('Unknown');
        $group[$item['roleName']][$item['gibbonAdmissionsAccountID']] = "{$name} ({$item['email']})";
        return $group;
    }, []);

    $form->toggleVisibilityByClass('typeAccount')->onSelect('applicationType')->when('account');

    $row = $form->addRow()->addClass('typeAccount');
        $row->addLabel('gibbonAdmissionsAccountID', __('Admissions Account'));
        $row->addSelect('gibbonAdmissionsAccountID')->fromArray($accounts)->required()->placeholder();

    $sql = "SELECT gibbonRole.category as groupBy, gibbonPersonID as value, CONCAT(gibbonPerson.surname, ', ', gibbonPerson.preferredName, ' (', gibbonPerson.username, ')') as name
            FROM gibbonPerson
            JOIN gibbonRole ON (gibbonRole.gibbonRoleID=gibbonPerson.gibbonRoleIDPrimary)
            WHERE gibbonRole.category <> 'Student'
            AND gibbonPerson.status='Full'
            ORDER BY gibbonPerson.surname, gibbonPerson.preferredName";

    $form->toggleVisibilityByClass('typePerson')->onSelect('applicationType')->when('person');

    $row = $form->addRow()->addClass('typePerson');
        $row->addLabel('gibbonPersonID', __('Person'));
        $row->addSelect('gibbonPersonID')->fromQuery($pdo, $sql, [], 'groupBy')->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
