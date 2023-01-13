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

namespace Gibbon\Domain\Staff;

use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;
use Gibbon\Domain\Traits\TableAware;

/**
 * Staff Coverage Date Gateway
 *
 * @version v18
 * @since   v18
 */
class StaffCoverageDateGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'gibbonStaffCoverageDate';
    private static $primaryKey = 'gibbonStaffCoverageDateID';

    private static $searchableColumns = [''];

    public function selectDatesByCoverage($gibbonStaffCoverageID)
    {
        $gibbonStaffCoverageIDList = is_array($gibbonStaffCoverageID)? $gibbonStaffCoverageID : [$gibbonStaffCoverageID];
        $data = ['gibbonStaffCoverageIDList' => implode(',', $gibbonStaffCoverageIDList) ];
        $sql = "SELECT gibbonStaffCoverageDate.gibbonStaffCoverageID as groupBy,  gibbonStaffCoverageDate.*, gibbonStaffCoverage.gibbonStaffCoverageID, gibbonStaffCoverage.status as coverage, gibbonStaffCoverage.requestType, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage, coverage.gibbonPersonID as gibbonPersonIDCoverage, gibbonStaffCoverageDate.reason as notes
                FROM gibbonStaffCoverageDate
                LEFT JOIN gibbonStaffCoverage ON (gibbonStaffCoverage.gibbonStaffCoverageID=gibbonStaffCoverageDate.gibbonStaffCoverageID)
                LEFT JOIN gibbonPerson AS coverage ON (gibbonStaffCoverage.gibbonPersonIDCoverage=coverage.gibbonPersonID)
                WHERE FIND_IN_SET(gibbonStaffCoverageDate.gibbonStaffCoverageID, :gibbonStaffCoverageIDList)
                ORDER BY gibbonStaffCoverageDate.date, gibbonStaffCoverageDate.timeStart";

        return $this->db()->select($sql, $data);
    }

    public function getCoverageDateDetailsByID($gibbonStaffCoverageDateID)
    {
        $data = ['gibbonStaffCoverageDateID' => $gibbonStaffCoverageDateID];
        $sql = "SELECT gibbonStaffCoverage.gibbonStaffCoverageID, gibbonStaffCoverage.status, gibbonStaffAbsence.gibbonStaffAbsenceID, gibbonStaffAbsenceType.name as type, gibbonStaffAbsence.reason, gibbonStaffCoverage.substituteTypes,
                gibbonStaffCoverageDate.date, gibbonStaffCoverageDate.allDay, gibbonStaffCoverageDate.timeStart, gibbonStaffCoverageDate.timeEnd, gibbonStaffCoverage.timestampStatus, gibbonStaffCoverage.timestampCoverage, gibbonStaffCoverage.requestType,
                gibbonStaffCoverage.notesCoverage, gibbonStaffCoverage.notesStatus, 0 as urgent, gibbonStaffAbsence.notificationSent, gibbonStaffAbsence.gibbonGroupID, gibbonStaffCoverage.notificationList as notificationListCoverage, gibbonStaffAbsence.notificationList as notificationListAbsence, 
                gibbonStaffCoverage.gibbonPersonID, absence.title AS titleAbsence, absence.preferredName AS preferredNameAbsence, absence.surname AS surnameAbsence, 
                gibbonStaffCoverage.gibbonPersonIDStatus, status.title AS titleStatus, status.preferredName AS preferredNameStatus, status.surname AS surnameStatus, 
                gibbonStaffCoverage.gibbonPersonIDCoverage, coverage.title as titleCoverage, coverage.preferredName as preferredNameCoverage, coverage.surname as surnameCoverage, gibbonStaffCoverageDate.foreignTable, gibbonStaffCoverageDate.foreignTableID
            FROM gibbonStaffCoverageDate 
            JOIN gibbonStaffCoverage ON (gibbonStaffCoverageDate.gibbonStaffCoverageID=gibbonStaffCoverage.gibbonStaffCoverageID)
            LEFT JOIN gibbonStaffAbsence ON (gibbonStaffAbsence.gibbonStaffAbsenceID=gibbonStaffCoverage.gibbonStaffAbsenceID)
            LEFT JOIN gibbonStaffAbsenceType ON (gibbonStaffAbsence.gibbonStaffAbsenceTypeID=gibbonStaffAbsenceType.gibbonStaffAbsenceTypeID)
            LEFT JOIN gibbonPerson AS coverage ON (gibbonStaffCoverage.gibbonPersonIDCoverage=coverage.gibbonPersonID)
            LEFT JOIN gibbonPerson AS status ON (gibbonStaffCoverage.gibbonPersonIDStatus=status.gibbonPersonID)
            LEFT JOIN gibbonPerson AS absence ON (gibbonStaffCoverage.gibbonPersonID=absence.gibbonPersonID)
            WHERE gibbonStaffCoverageDate.gibbonStaffCoverageDateID=:gibbonStaffCoverageDateID
            ";

        return $this->db()->selectOne($sql, $data);
    }

    public function selectTimetabledClassCoverageByPersonAndDate($gibbonSchoolYearID, $gibbonPersonID, $dateStart, $dateEnd)
    {
        $data = ['gibbonSchoolYearID' => $gibbonSchoolYearID, 'gibbonPersonID' => $gibbonPersonID, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd];
        $sql = "SELECT DISTINCT gibbonTTDayDate.date, gibbonTT.gibbonTTID, gibbonTT.name, gibbonTTDayRowClass.gibbonTTDayRowClassID, gibbonCourseClass.gibbonCourseClassID, gibbonCourseClass.nameShort as classNameShort, gibbonTTColumnRow.name as columnName, gibbonTTColumnRow.timeStart, gibbonTTColumnRow.timeEnd, gibbonCourse.name as courseName, gibbonCourse.nameShort as courseNameShort, gibbonStaffCoverage.gibbonStaffCoverageID, gibbonStaffCoverage.status, CONCAT(gibbonTTDayDate.date, ':', gibbonTTDayRowClass.gibbonTTDayRowClassID) as timetableClassPeriod, coverage.surname as surnameCoverage, coverage.preferredName as preferredNameCoverage
        FROM gibbonTT 
        JOIN gibbonTTDay ON (gibbonTT.gibbonTTID=gibbonTTDay.gibbonTTID) 
        JOIN gibbonTTDayRowClass ON (gibbonTTDayRowClass.gibbonTTDayID=gibbonTTDay.gibbonTTDayID) 
        JOIN gibbonTTDayDate ON (gibbonTTDay.gibbonTTDayID=gibbonTTDayDate.gibbonTTDayID) 
        JOIN gibbonCourseClass ON (gibbonTTDayRowClass.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID)
        JOIN gibbonTTColumnRow ON (gibbonTTColumnRow.gibbonTTColumnRowID=gibbonTTDayRowClass.gibbonTTColumnRowID)
        JOIN gibbonCourseClassPerson ON (gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID)
        JOIN gibbonCourse ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID)
        LEFT JOIN gibbonStaffCoverageDate ON (gibbonStaffCoverageDate.foreignTableID=gibbonTTDayRowClass.gibbonTTDayRowClassID AND gibbonStaffCoverageDate.foreignTable='gibbonTTDayRowClass' AND gibbonStaffCoverageDate.date=gibbonTTDayDate.date)
        LEFT JOIN gibbonStaffCoverage ON (gibbonStaffCoverage.gibbonStaffCoverageID=gibbonStaffCoverageDate.gibbonStaffCoverageID AND gibbonStaffCoverage.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID)
        LEFT JOIN gibbonPerson as coverage ON (coverage.gibbonPersonID=gibbonStaffCoverage.gibbonPersonIDCoverage)
        LEFT JOIN gibbonTTDayRowClassException ON (gibbonTTDayRowClassException.gibbonTTDayRowClassID=gibbonTTDayRowClass.gibbonTTDayRowClassID AND gibbonTTDayRowClassException.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID)
        WHERE gibbonCourseClassPerson.gibbonPersonID=:gibbonPersonID 
        AND gibbonCourse.gibbonSchoolYearID=:gibbonSchoolYearID 
        AND gibbonTT.active='Y' 
        AND gibbonTTDayDate.date BETWEEN :dateStart AND :dateEnd
        AND gibbonCourseClassPerson.role NOT LIKE '%Left'
        AND gibbonTTDayRowClassExceptionID IS NULL
        ORDER BY gibbonTTDayDate.date, gibbonTTColumnRow.timeStart ASC";

        return $this->db()->select($sql, $data);
    }

    public function selectPotentialCoverageByPersonAndDate($gibbonSchoolYearID, $gibbonPersonID, $dateStart, $dateEnd)
    {
        $query = $this
            ->newSelect()
            ->cols(['gibbonTT.gibbonTTID as groupBy', '"Class" as context', 'CONCAT(gibbonCourse.nameShort, ".", gibbonCourseClass.nameShort) as contextName', '"gibbonTTDayRowClass" as foreignTable', 'gibbonTTDayRowClass.gibbonTTDayRowClassID as foreignTableID', 'gibbonStaffCoverage.gibbonStaffCoverageID', 'gibbonTTDayDate.date', 'gibbonTTColumnRow.name as period', 'gibbonTTColumnRow.timeStart', 'gibbonTTColumnRow.timeEnd', 'gibbonStaffCoverage.status as coverage', 'gibbonStaffCoverage.gibbonPersonIDCoverage', 'coverage.surname as surnameCoverage', 'coverage.preferredName as preferredNameCoverage' ])
            ->from('gibbonTT')
            ->innerJoin('gibbonTTDay', 'gibbonTT.gibbonTTID=gibbonTTDay.gibbonTTID') 
            ->innerJoin('gibbonTTDayRowClass', 'gibbonTTDayRowClass.gibbonTTDayID=gibbonTTDay.gibbonTTDayID') 
            ->innerJoin('gibbonTTDayDate', 'gibbonTTDay.gibbonTTDayID=gibbonTTDayDate.gibbonTTDayID') 
            ->innerJoin('gibbonCourseClass', 'gibbonTTDayRowClass.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID')
            ->innerJoin('gibbonTTColumnRow', 'gibbonTTColumnRow.gibbonTTColumnRowID=gibbonTTDayRowClass.gibbonTTColumnRowID')
            ->innerJoin('gibbonCourseClassPerson', 'gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID')
            ->innerJoin('gibbonCourse', 'gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID')
            ->leftJoin('gibbonStaffCoverageDate', 'gibbonStaffCoverageDate.foreignTableID=gibbonTTDayRowClass.gibbonTTDayRowClassID AND gibbonStaffCoverageDate.foreignTable="gibbonTTDayRowClass" AND gibbonStaffCoverageDate.date=gibbonTTDayDate.date')
            ->leftJoin('gibbonStaffCoverage', 'gibbonStaffCoverage.gibbonStaffCoverageID=gibbonStaffCoverageDate.gibbonStaffCoverageID AND gibbonStaffCoverage.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID')
            ->leftJoin('gibbonPerson as coverage', 'coverage.gibbonPersonID=gibbonStaffCoverage.gibbonPersonIDCoverage')
            ->leftJoin('gibbonTTDayRowClassException', 'gibbonTTDayRowClassException.gibbonTTDayRowClassID=gibbonTTDayRowClass.gibbonTTDayRowClassID AND gibbonTTDayRowClassException.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID')
            ->where('gibbonCourseClassPerson.gibbonPersonID=:gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID)
            ->where('gibbonCourse.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('gibbonTT.active="Y"')
            ->where('gibbonTTDayDate.date BETWEEN :dateStart AND :dateEnd')
            ->bindValues(['dateStart' => $dateStart, 'dateEnd' => $dateEnd])
            ->where('gibbonCourseClassPerson.role NOT LIKE "%Left"')
            ->where('gibbonTTDayRowClassExceptionID IS NULL')
            ->where('gibbonStaffCoverage.status <> "Cancelled" AND gibbonStaffCoverage.status <> "Declined"');

        $query->unionAll()
            ->cols([
                'gibbonStaffDuty.gibbonStaffDutyID as groupBy', '"Staff Duty" as context', 'gibbonStaffDuty.name as contextName', '"gibbonStaffDutyPerson" as foreignTable', 'gibbonStaffDutyPerson.gibbonStaffDutyPersonID as foreignTableID', 'gibbonStaffCoverage.gibbonStaffCoverageID',  "DATE_ADD(:dateStart, INTERVAL (((1-DAYOFWEEK(:dateStart)) % 7)+gibbonDaysOfWeek.gibbonDaysOfWeekID) % 7 DAY) as date", '"Staff Duty" as period', 'gibbonStaffDuty.timeStart', 'gibbonStaffDuty.timeEnd', 'gibbonStaffCoverage.status as coverage', 'gibbonStaffCoverage.gibbonPersonIDCoverage', 'coverage.surname as surnameCoverage, coverage.preferredName as preferredNameCoverage'
            ])
            ->from('gibbonStaffDutyPerson')
            ->innerJoin('gibbonStaffDuty', 'gibbonStaffDuty.gibbonStaffDutyID=gibbonStaffDutyPerson.gibbonStaffDutyID')
            ->innerJoin('gibbonDaysOfWeek', 'gibbonDaysOfWeek.gibbonDaysOfWeekID=gibbonStaffDutyPerson.gibbonDaysOfWeekID')
            ->innerJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=gibbonStaffDutyPerson.gibbonPersonID')

            ->leftJoin('gibbonStaffCoverageDate', 'gibbonStaffCoverageDate.foreignTableID=gibbonStaffDutyPerson.gibbonStaffDutyPersonID AND gibbonStaffCoverageDate.foreignTable="gibbonStaffDutyPerson" AND gibbonStaffCoverageDate.date BETWEEN :dateStart AND :dateEnd')
            ->leftJoin('gibbonStaffCoverage', 'gibbonStaffCoverage.gibbonStaffCoverageID=gibbonStaffCoverageDate.gibbonStaffCoverageID AND gibbonStaffCoverage.gibbonPersonID=gibbonStaffDutyPerson.gibbonPersonID')
            ->leftJoin('gibbonPerson as coverage', 'coverage.gibbonPersonID=gibbonStaffCoverage.gibbonPersonIDCoverage')

            ->where('gibbonStaffDutyPerson.gibbonPersonID=:gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID)
            ->where('gibbonPerson.status="Full"')
            ->where('(gibbonDaysOfWeek.gibbonDaysOfWeekID-1) BETWEEN WEEKDAY(:dateStart) AND WEEKDAY(:dateEnd)')
            ->bindValues(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);

        $query->unionAll()
            ->cols([
                'gibbonActivitySlot.gibbonActivitySlotID as groupBy', '"Activity" as context', 'gibbonActivity.name as contextName', '"gibbonActivitySlot" as foreignTable', 'gibbonActivitySlot.gibbonActivitySlotID as foreignTableID', 'gibbonStaffCoverage.gibbonStaffCoverageID',  "DATE_ADD(:dateStart, INTERVAL (((1-DAYOFWEEK(:dateStart)) % 7)+gibbonDaysOfWeek.gibbonDaysOfWeekID) % 7 DAY) as date", '"Activity" as period', 'gibbonActivitySlot.timeStart', 'gibbonActivitySlot.timeEnd', 'gibbonStaffCoverage.status as coverage', 'gibbonStaffCoverage.gibbonPersonIDCoverage', 'coverage.surname as surnameCoverage', 'coverage.preferredName as preferredNameCoverage'
            ])
            ->from('gibbonActivityStaff')
            ->innerJoin('gibbonActivity', 'gibbonActivity.gibbonActivityID=gibbonActivityStaff.gibbonActivityID')
            ->innerJoin('gibbonActivitySlot', 'gibbonActivitySlot.gibbonActivityID=gibbonActivity.gibbonActivityID')
            ->innerJoin('gibbonDaysOfWeek', 'gibbonDaysOfWeek.gibbonDaysOfWeekID=gibbonActivitySlot.gibbonDaysOfWeekID')
            ->innerJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=gibbonActivityStaff.gibbonPersonID')

            ->leftJoin('gibbonStaffCoverageDate', 'gibbonStaffCoverageDate.foreignTableID=gibbonActivitySlot.gibbonActivitySlotID AND gibbonStaffCoverageDate.foreignTable="gibbonActivitySlot" AND gibbonStaffCoverageDate.date BETWEEN :dateStart AND :dateEnd')
            ->leftJoin('gibbonStaffCoverage', 'gibbonStaffCoverage.gibbonStaffCoverageID=gibbonStaffCoverageDate.gibbonStaffCoverageID AND gibbonStaffCoverage.gibbonPersonID=gibbonActivityStaff.gibbonPersonID')
            ->leftJoin('gibbonPerson as coverage', 'coverage.gibbonPersonID=gibbonStaffCoverage.gibbonPersonIDCoverage')

            ->where('gibbonActivityStaff.gibbonPersonID=:gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID)
            ->where('gibbonPerson.status="Full"')
            ->where('(gibbonDaysOfWeek.gibbonDaysOfWeekID-1) BETWEEN WEEKDAY(:dateStart) AND WEEKDAY(:dateEnd)')
            ->bindValues(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);

            $query->orderBy(['date', 'timeStart']);

        return $this->runSelect($query);
    }

    public function selectCoverageTimesByDate($gibbonSchoolYearID, $date)
    {
        $query = $this
            ->newSelect()
            ->cols(['gibbonTTColumnRow.gibbonTTColumnRowID as groupBy', 'gibbonTTColumnRow.type', 'gibbonTTColumnRow.name as period', 'gibbonTTColumnRow.timeStart', 'gibbonTTColumnRow.timeEnd'])
            ->from('gibbonTT')
            ->innerJoin('gibbonTTDay', 'gibbonTT.gibbonTTID=gibbonTTDay.gibbonTTID') 
            ->innerJoin('gibbonTTDayDate', 'gibbonTTDay.gibbonTTDayID=gibbonTTDayDate.gibbonTTDayID') 
            ->innerJoin('gibbonTTColumnRow', 'gibbonTTColumnRow.gibbonTTColumnID=gibbonTTDay.gibbonTTColumnID')
            ->where('gibbonTT.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('gibbonTT.active="Y"')
            ->where('gibbonTTDayDate.date=:date')
            ->where('(gibbonTTColumnRow.type="Lesson" OR gibbonTTColumnRow.type="Pastoral")')
            ->bindValue('date', $date);

        $query->unionAll()
            ->cols(['gibbonStaffDuty.gibbonStaffDutyID as groupBy', '"Staff Duty" AS type', 'gibbonStaffDuty.name as period', 'gibbonStaffDuty.timeStart', 'gibbonStaffDuty.timeEnd'])
            ->from('gibbonStaffDutyPerson')
            ->innerJoin('gibbonStaffDuty', 'gibbonStaffDuty.gibbonStaffDutyID=gibbonStaffDutyPerson.gibbonStaffDutyID')
            ->innerJoin('gibbonDaysOfWeek', 'gibbonDaysOfWeek.gibbonDaysOfWeekID=gibbonStaffDutyPerson.gibbonDaysOfWeekID')
            ->where('(gibbonDaysOfWeek.gibbonDaysOfWeekID-1) = WEEKDAY(:date)')
            ->bindValue('date', $date)
            ->groupBy(['gibbonStaffDuty.gibbonStaffDutyID']);

        $query->unionAll()
            ->cols(['gibbonDaysOfWeek.gibbonDaysOfWeekID as groupBy', '"Activity" AS type', '"Activity" as period', 'gibbonActivitySlot.timeStart', 'gibbonActivitySlot.timeEnd'])
            ->from('gibbonActivitySlot')
            ->innerJoin('gibbonActivity', 'gibbonActivitySlot.gibbonActivityID=gibbonActivity.gibbonActivityID')
            ->innerJoin('gibbonDaysOfWeek', 'gibbonDaysOfWeek.gibbonDaysOfWeekID=gibbonActivitySlot.gibbonDaysOfWeekID')
            ->where('(gibbonDaysOfWeek.gibbonDaysOfWeekID-1) = WEEKDAY(:date)')
            ->bindValue('date', $date)
            ->groupBy(['gibbonDaysOfWeek.gibbonDaysOfWeekID']);

        $query->orderBy(['timeStart', 'timeEnd']);

        return $this->runSelect($query);
    }

    public function getCoverageTimesByForeignTable($foreignTable, $foreignTableID, $date)
    {
        switch ($foreignTable) {
            case 'gibbonTTDayRowClass': 
                return $this->getCoverageTimesByTimetableClass($foreignTableID);
            case 'gibbonStaffDutyPerson': 
                return $this->getCoverageTimesByStaffDuty($foreignTableID, $date);
            case 'gibbonActivitySlot': 
                return $this->getCoverageTimesByActivity($foreignTableID, $date);
            default:
                return [];
        }
    }

    public function getCoverageTimesByTimetableClass($gibbonTTDayRowClassID)
    {
        $data = ['gibbonTTDayRowClassID' => $gibbonTTDayRowClassID];
        $sql = "SELECT gibbonTTColumnRow.name as period, CONCAT(gibbonCourse.nameShort, '.', gibbonCourseClass.nameShort) as contextName, gibbonTTColumnRow.timeStart, gibbonTTColumnRow.timeEnd, 'N' as allDay, gibbonCourse.nameShort as courseName, gibbonCourseClass.nameShort as className, gibbonSpace.name as spaceName, gibbonSpace.gibbonSpaceID, gibbonCourseClass.gibbonCourseClassID
            FROM gibbonTTDayRowClass
            JOIN gibbonTTColumnRow ON (gibbonTTColumnRow.gibbonTTColumnRowID=gibbonTTDayRowClass.gibbonTTColumnRowID)
            JOIN gibbonCourseClass ON (gibbonTTDayRowClass.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID)
            JOIN gibbonCourse ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID)
            LEFT JOIN gibbonSpace ON (gibbonTTDayRowClass.gibbonSpaceID=gibbonSpace.gibbonSpaceID)
            WHERE gibbonTTDayRowClassID=:gibbonTTDayRowClassID";
        
        return $this->db()->selectOne($sql, $data);
    }

    public function getCoverageTimesByStaffDuty($gibbonStaffDutyPersonID, $date)
    {
        $data = ['gibbonStaffDutyPersonID' => $gibbonStaffDutyPersonID, 'date' => $date];
        $sql = "SELECT 'Staff Duty' as period, gibbonStaffDuty.name as contextName, gibbonStaffDuty.timeStart, gibbonStaffDuty.timeEnd, 'N' as allDay 
            FROM gibbonStaffDutyPerson
            JOIN gibbonStaffDuty ON (gibbonStaffDuty.gibbonStaffDutyID=gibbonStaffDutyPerson.gibbonStaffDutyID)
            JOIN gibbonDaysOfWeek ON (gibbonDaysOfWeek.gibbonDaysOfWeekID=gibbonStaffDutyPerson.gibbonDaysOfWeekID)
            WHERE gibbonStaffDutyPerson.gibbonStaffDutyPersonID=:gibbonStaffDutyPersonID
            AND gibbonDaysOfWeek.gibbonDaysOfWeekID-1 = WEEKDAY(:date)";
        
        return $this->db()->selectOne($sql, $data);
    }

    public function getCoverageTimesByActivity($gibbonActivitySlotID, $date)
    {
        $data = ['gibbonActivitySlotID' => $gibbonActivitySlotID, 'date' => $date];
        $sql = "SELECT 'Activity' as period, gibbonActivity.name as contextName, gibbonActivitySlot.timeStart, gibbonActivitySlot.timeEnd, 'N' as allDay
            FROM gibbonActivitySlot
            JOIN gibbonActivity ON (gibbonActivitySlot.gibbonActivityID=gibbonActivity.gibbonActivityID)
            JOIN gibbonDaysOfWeek ON (gibbonDaysOfWeek.gibbonDaysOfWeekID=gibbonActivitySlot.gibbonDaysOfWeekID)
            WHERE gibbonActivitySlot.gibbonActivitySlotID=:gibbonActivitySlotID
            AND gibbonDaysOfWeek.gibbonDaysOfWeekID-1 = WEEKDAY(:date)";
        
        return $this->db()->selectOne($sql, $data);
    }

    public function deleteCoverageDatesByAbsenceID($gibbonStaffAbsenceID)
    {
        $data = ['gibbonStaffAbsenceID' => $gibbonStaffAbsenceID];
        $sql = "DELETE gibbonStaffCoverageDate FROM gibbonStaffCoverageDate
                JOIN gibbonStaffAbsenceDate ON (gibbonStaffAbsenceDate.gibbonStaffAbsenceDateID=gibbonStaffCoverageDate.gibbonStaffAbsenceDateID)
                WHERE gibbonStaffAbsenceDate.gibbonStaffAbsenceID = :gibbonStaffAbsenceID";

        return $this->db()->delete($sql, $data);
    }
}
