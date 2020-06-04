<?php declare(strict_types = 1);

namespace InpUserParser;

/*
 * Request Class Handles the getting of Users Details and sending to useragent
 * After Forming the needed HTML
 * Tests are not available yet cause of the many dependencies
 * */

class Request
{

//    Request Types Handled by request
    public static function requestTypes() : array
    {
        return [
            'all',
            'id',
            'search',
        ];
    }

//    Handle Function called by wordpress on associated ajax calls
    public static function handle()
    {
        check_ajax_referer('inpuserparser_hook', 'nonce');
        if (isset($_POST['requestType']) && !empty($_POST['requestType'])) {//input var okay
            $requestType = $_POST['requestType'];
        }

//        Check if sent request type is amongst is valid and sends it dynamically to relevant functions
        if (in_array($requestType, self::requestTypes())) {
            echo json_encode(['success' => 'true', 'reply' => self::$requestType()]);
            wp_die();
        }
        wp_die();// Exit Request Without Reason...
    }

//    Handles id request
//    This request is for a particular user by the Id
    public static function id() : array
    {
        if (!(isset($_POST['id']) && !empty($_POST['id']))) {//input var okay
            wp_die(); // Exit Without Reason...
        }
        $id = (int) $_POST['id'];

//        - Get all userJson from User class
//        - Passes it to users() to get an array of User Objects
//        - Iterates through it to get a key value pair of users properties and values
        return User::users(User::userJson($id))[0]->iterateArray();
    }

//    Search for a particular user by the sent query String
    public static function search() : string
    {
        if (!(isset($_POST['searchStr']) && !empty($_POST['searchStr']))) {//input var okay
            wp_die(); // Exit Without Reason...
        }
        if (!(isset($_POST['column']) && !empty($_POST['column']))) {//input var okay
            wp_die(); // Exit Without Reason...
        }
        $searchStr = $_POST['searchStr'];
        $column = $_POST['column'];

        if (in_array($column, User::usedFields())) {
            $users = User::search((string) $searchStr, (string) $column, User::users(User::usersJson()));

            if (empty($users)) {
                return "<p class='text-info'>Search param '{$searchStr}' Does not Match Any "
                    . Settings::ucFields($column) . '</p>';
            }
            return self::generateTable($users);
            wp_die();
        }

        wp_die();//Exit Without Reason
    }

//    Returns all Users
    public static function all() : string
    {
        return self::generateTable(User::users(User::usersJson()));
    }

//    Generate HTML Table Markup for Users
    public static function generateTable(array $users) : string
    {
        $output = '<table class="table table-hover">';
        $output.= '<thead><tr>';

        foreach (Settings::visibleColumns() as $column) {
            $output.= '<th scope="col">' . Settings::ucFields($column) . '</th>';
        }
        $output.= '</tr></thead>';
        $output.= '<tbody>';

        for ($i=0; $i<sizeof($users); $i++) {
            $output.='<tr>';
            foreach (Settings::visibleColumns() as $column) {
                if ((string) $column === (string) 'id') {
                    $output.= '<th scope="row"><a href="" onclick="showDetail('
                        .$users[$i]->valueOf((string) 'id').'); return false;">'
                        . $users[$i]->valueOf((string) $column)
                        .'</th>';
                }
                if (!((string) $column === (string) 'id')) {
                    $output.= '<td><a href="" onclick="showDetail('
                        .$users[$i]->valueOf((string) 'id').'); return false;">'
                        .$users[$i]->valueOf((string) $column).'</td>';
                }
            }
            $output.="</tr>";
        }
        $output.='</tbody></table>';
        return $output;
    }
}
