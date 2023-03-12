<style>
    form {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        max-width: 800px;
        margin: 0 auto;
    }

    label, input {
        width: 100%;
        margin-bottom: 10px;
    }

    @media only screen and (min-width: 768px) {
        label, input {
            width: 100%;
        }
    }

    @media only screen and (min-width: 1024px) {
        label, input {
            width: 100%;
        }
    }
</style>
    <?php
		if ( ! function_exists( 'wp_redirect' ) ) {
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}

		function create_contacts_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'contacts';

		$sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        date_of_birth DATE NOT NULL,
        user_image VARCHAR(255),
        age VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	register_activation_hook( __FILE__, 'create_contacts_table' );


	function contacts_form() {
		global $wpdb;

		if (isset($_POST['contact_id']) && !isset($_POST['update_contact'])) {
			$contact_id = $_POST['contact_id'];
			$table_name = $wpdb->prefix . 'contacts';
			$result = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $contact_id");
			?>
            <form method="post" enctype="multipart/form-data">
                <h1 style="text-align: center">Update Contact</h1>
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" value="<?php echo $result->first_name; ?>" required><br>

                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" value="<?php echo $result->last_name; ?>" required><br>

                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $result->email; ?>" required><br>

                <label for="mobile">Mobile:</label>
                <input type="tel" name="mobile" value="<?php echo $result->mobile; ?>" required><br>

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $result->date_of_birth; ?>" required><br>

                <label for="user_image">User Image:</label>
				<?php if ( $result->user_image ) : ?>
                    <img style="width: 50px" src="<?php echo esc_attr( $result->user_image ); ?>" alt="<?php echo esc_attr( $result->first_name . ' ' . $result->last_name ); ?>">
				<?php else: ?>
                    <span>No image available</span>
				<?php endif; ?>

                <input type="file" name="user_image"><br>

                <input type="text" name="contact_id" value="<?php echo $result->id; ?>">
                <input type="submit" name="update_contact" value="Update">
            </form>

			<?php

		}else if(isset($_POST['update_contact'])) {
			// get the contact ID from the hidden input field
			$contact_id = $_POST['contact_id'];

			$table_name = $wpdb->prefix . 'contacts';

			$first_name = $_POST['first_name'];
			$last_name = $_POST['last_name'];
			$email = $_POST['email'];
			$mobile = $_POST['mobile'];
			$date_of_birth = $_POST['date_of_birth'];

			try {
				$now = new DateTime();
				$dob = new DateTime($date_of_birth);
				$diff = $now->diff($dob);

				// Get the age in years, months, and days
				$age_years = $diff->y;
				$age_months = $diff->m;
				$age_days = $diff->d;

				// Output the age
				$age = $age_years . ' years, ' . $age_months . ' months, and ' . $age_days . ' days';
			} catch (Exception $e) {
				// Handle the exception (e.g., display an error message)
				$age = 'Error: Invalid date of birth';
			}

			$user_image = '';

// Check if file was uploaded successfully
			if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
				$file_name = $_FILES['user_image']['name'];
				$file_tmp_name = $_FILES['user_image']['tmp_name'];
				$file_size = $_FILES['user_image']['size'];
				$file_type = $_FILES['user_image']['type'];

				// Move uploaded file to a permanent location
				$upload_dir = wp_upload_dir(); // WordPress upload directory
				$file_path = $upload_dir['path'] . '/' . $file_name;
				move_uploaded_file($file_tmp_name, $file_path);

				$user_image = $upload_dir['url'] . '/' . $file_name; // URL of uploaded file
			} else {
				// Get the current user image URL from the database
				$user_image = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT user_image FROM $table_name WHERE id = %d",
						$contact_id
					)
				);
			}

			$wpdb->update(
				$table_name,
				array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'mobile' => $mobile,
					'date_of_birth' => $date_of_birth,
					'user_image' => $user_image,
					'age' => $age
				),
				array(
					'id' => $contact_id // Update contact with ID of 11
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				),
				array(
					'%d' // ID is an integer, so use '%d'
				)
			);

			//wp_redirect( admin_url( '/admin.php?page=contacts_list' ) );
			wp_redirect( admin_url( '/admin.php?page=contacts_list' ) );
			exit;
		}



		else if (isset($_POST['submit'])) {
			$table_name = $wpdb->prefix . 'contacts';

			$first_name = $_POST['first_name'];
			$last_name = $_POST['last_name'];
			$email = $_POST['email'];
			$mobile = $_POST['mobile'];
			$date_of_birth = $_POST['date_of_birth'];

			try {
				$now = new DateTime();
				$dob = new DateTime($date_of_birth);
				$diff = $now->diff($dob);

				// Get the age in years, months, and days
				$age_years = $diff->y;
				$age_months = $diff->m;
				$age_days = $diff->d;

				// Output the age
				$age = $age_years . ' years, ' . $age_months . ' months, and ' . $age_days . ' days';
			} catch (Exception $e) {
				// Handle the exception (e.g., display an error message)
				$age = 'Error: Invalid date of birth';
			}


			$user_image = '';

			// Check if file was uploaded successfully
			if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
				$file_name = $_FILES['user_image']['name'];
				$file_tmp_name = $_FILES['user_image']['tmp_name'];
				$file_size = $_FILES['user_image']['size'];
				$file_type = $_FILES['user_image']['type'];

				// Move uploaded file to a permanent location
				$upload_dir = wp_upload_dir(); // WordPress upload directory
				$file_path = $upload_dir['path'] . '/' . $file_name;
				move_uploaded_file($file_tmp_name, $file_path);

				$user_image = $upload_dir['url'] . '/' . $file_name; // URL of uploaded file
			}

			$wpdb->insert(
				$table_name,
				array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'mobile' => $mobile,
					'date_of_birth' => $date_of_birth,
					'user_image' => $user_image,
					'age' => $age
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				)
			);

		}else{
		    ?>

            <form method="post" enctype="multipart/form-data">
                <h1 style="text-align: center">
                    Create Contacts
                </h1>
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" required><br>

                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" required><br>

                <label for="email">Email:</label>
                <input type="email" name="email" required><br>

                <label for="mobile">Mobile:</label>
                <input type="tel" name="mobile" required><br>

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth"  required><br>

                <label for="user_image">User Image:</label>


                <input type="file" name="user_image"><br>


                <input type="submit" name="submit" value="Submit">
            </form>

            <?php
        }

	}

	function contacts_list() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'contacts';

		// Get total number of contacts
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		// Set the number of contacts to display per page
		$per_page = 10;

		// Get the current page number
		$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

		// Calculate the offset
		$offset = ( $current_page - 1 ) * $per_page;

		// Retrieve the contacts from the database with pagination
		$results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT $per_page OFFSET $offset" );

		// Output the contact list
		?>
		<div class="wrap">
			<h1>Contacts</h1>
			<table class="wp-list-table widefat striped">
				<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">First Name</th>
					<th scope="col">Last Name</th>
					<th scope="col">Email</th>
					<th scope="col">Mobile</th>
					<th scope="col">Date of Birth</th>
					<th scope="col">User Image</th>
					<th scope="col">Age</th>
                    <th scope="col">Action</th>

                </tr>
				</thead>
				<tbody>
				<?php foreach ( $results as $result ) : ?>
					<tr>
						<td><?php echo $result->id; ?></td>
						<td><?php echo $result->first_name; ?></td>
						<td><?php echo $result->last_name; ?></td>
						<td><?php echo $result->email; ?></td>
						<td><?php echo $result->mobile; ?></td>
						<td><?php echo $result->date_of_birth; ?></td>
						<td>
							<?php if ( $result->user_image ) : ?>
								<img style="width: 50px" src="<?php echo esc_attr( $result->user_image ); ?>" alt="<?php echo esc_attr( $result->first_name . ' ' . $result->last_name ); ?>">
							<?php else: ?>
								<span>No image available</span>
							<?php endif; ?>
						</td>
						<td><?php echo $result->age; ?></td>
                        <td>
                            <form method="post" action="<?php echo admin_url('admin.php?page=contacts'); ?>">
                                <input type="hidden" name="contact_id" value="<?php echo $result->id; ?>">
                                <button type="submit" name="edit_contact" class="button-link">Edit</button>
                            </form>



                        <form method="post">
                                <input type="hidden" name="contact_id" value="<?php echo $result->id; ?>">
                                <button type="submit" name="delete_contact" class="button-link" onclick="return confirm('Are you sure you want to delete this contact?')">Delete</button>
                         </form>

                        </td>

                    </tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

		// Function to handle delete contact request
		if ( isset( $_POST['delete_contact'] ) ) {

			$table_name = $wpdb->prefix . 'contacts';

			// Get the contact ID from the form submission
			$contact_id = absint( $_POST['contact_id'] );

			// Delete the contact from the database
			$wpdb->delete( $table_name, array( 'id' => $contact_id ) );

			// Redirect back to the contacts list page
			wp_redirect( admin_url( '/admin.php?page=contacts_list' ) );
			exit;
		}


		function contacts_menu() {
		add_menu_page(
			'Contacts', // page title
			'Contacts', // menu title
			'manage_options', // capability
			'contacts', // menu slug
			'contacts_form' // function to display the form
		);
		add_submenu_page(
			'contacts', // parent slug
			'Contacts List', // page title
			'Contacts List', // menu title
			'manage_options', // capability
			'contacts_list', // menu slug
			'contacts_list' // function to display the list
		);

		}



		// create the contacts table on plugin activation
	register_activation_hook( __FILE__, 'create_contacts_table' );

// add the contacts menu to the WordPress admin menu
	add_action( 'admin_menu', 'contacts_menu' );

	?>





